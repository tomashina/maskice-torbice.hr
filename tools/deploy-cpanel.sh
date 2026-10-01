#!/usr/bin/env bash

set -Eeuo pipefail

readonly EXPECTED_TARGET='/home/maskice2/maskice-torbice.hr/upload'
readonly BACKUP_BASE='/home/maskice2/.deploy-backups/maskice-torbice.hr'

fail() {
    printf 'Deployment prekinut: %s\n' "$1" >&2
    exit 2
}

dry_run=0

if [[ "${1:-}" == '--dry-run' ]]; then
    dry_run=1
    shift
fi
(( $# <= 1 )) || fail 'prevelik broj argumenata'

target="${1:-$EXPECTED_TARGET}"

[[ "$target" == "$EXPECTED_TARGET" ]] || fail "neocekivani produkcijski direktorij: $target"
[[ -d "$target" ]] || fail "produkcijski direktorij ne postoji: $target"

target="$(cd "$target" && pwd -P)"
expected_target="$(cd "$EXPECTED_TARGET" && pwd -P)"

[[ "$target" == "$expected_target" ]] || fail 'produkcijski direktorij izlazi iz ocekivanog document roota'
[[ -f "$target/config.php" ]] || fail 'nedostaje produkcijski config.php'
[[ -f "$target/admin/config.php" ]] || fail 'nedostaje produkcijski admin/config.php'

script_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd -P)"
repo_root="$(cd "$script_dir/.." && pwd -P)"

if [[ "$(git -C "$repo_root" rev-parse --is-inside-work-tree 2>/dev/null)" != 'true' ]]; then
    fail "izvor nije Git working tree: $repo_root"
fi

deploy_id="$(date -u '+%Y%m%dT%H%M%SZ')-$$"
backup_root="$BACKUP_BASE/$deploy_id"
copied_count=0
unchanged_count=0
protected_count=0
backup_count=0

umask 027

is_protected_path() {
    local relative_path="$1"

    case "$relative_path" in
        .git|.git/*|.gitignore|.gitattributes|.cpanel.yml|README|README.*|docs|docs/*|tools|tools/*|extensions|extensions/*)
            return 0
            ;;
        config.php|admin/config.php|php.ini|.user.ini|.env|.env.*|.htpasswd|.ftpquota|auth.json|credentials.json)
            return 0
            ;;
        storage|storage/*|system/storage|system/storage/*)
            return 0
            ;;
        image/catalog|image/catalog/*|image/cache|image/cache/*|image/*inbound*)
            return 0
            ;;
        sitemaps|sitemaps/*|upload|upload/*|.well-known|.well-known/*|cgi-bin|cgi-bin/*)
            return 0
            ;;
        admin/model/extension/payment/pp_express.php)
            return 0
            ;;
        admin/view/javascript/clicker_ckeditor/clicker/plugins/leaflet/Installation\ Guide.txt)
            return 0
            ;;
        admin/view/javascript/clicker_ckeditor/clicker/plugins/leaflet/dialogs/leaflet.js)
            return 0
            ;;
        catalog/model/extension/payment/alipay.php)
            return 0
            ;;
        catalog/view/javascript/mpgdpr/cookieconsent/cookieconsent.js)
            return 0
            ;;
        catalog/view/javascript/mpgdpr/cookieconsent/cookieconsent.min.js)
            return 0
            ;;
        catalog/view/javascript/mpgdpr/cookieconsent/cookieconsent.unmin.js)
            return 0
            ;;
        error_log|*/error_log|*.log|*.sql|*.sql.gz|*.dump|*.zip|*.tar|*.tar.gz|*.tgz|*.rar|*.7z|*.bak|*.old|*.orig)
            return 0
            ;;
    esac

    return 1
}

validate_relative_path() {
    local relative_path="$1"

    case "$relative_path" in
        ''|/*|..|../*|*/..|*/../*)
            fail "Git putanja nije sigurna za deployment: $relative_path"
            ;;
    esac
}

assert_safe_parent_path() {
    local relative_parent="$1"
    local cursor="$target"
    local part
    local old_ifs="$IFS"
    local -a parts=()

    IFS='/'
    read -r -a parts <<< "$relative_parent"
    IFS="$old_ifs"

    for part in "${parts[@]}"; do
        [[ -z "$part" || "$part" == '.' ]] && continue
        cursor="$cursor/$part"

        if [[ -L "$cursor" ]]; then
            fail "postojeci parent je simbolicka poveznica: $cursor"
        fi

        if [[ -e "$cursor" && ! -d "$cursor" ]]; then
            fail "postojeci parent nije direktorij: $cursor"
        fi
    done
}

# Cijeli tracked skup provjerava se prije prve promjene na produkciji.
while IFS= read -r -d '' relative_path; do
    if is_protected_path "$relative_path"; then
        protected_count=$((protected_count + 1))
        continue
    fi

    validate_relative_path "$relative_path"

    source_path="$repo_root/$relative_path"
    [[ -f "$source_path" ]] || fail "pracena datoteka ne postoji: $relative_path"
    [[ ! -L "$source_path" ]] || fail "izvorna simbolicka poveznica nije dopustena: $relative_path"

    relative_parent="${relative_path%/*}"
    [[ "$relative_parent" != "$relative_path" ]] || relative_parent='.'
    assert_safe_parent_path "$relative_parent"

    destination_path="$target/$relative_path"
    [[ ! -L "$destination_path" ]] || fail "odredisna datoteka je simbolicka poveznica: $relative_path"

    if [[ -e "$destination_path" && ! -f "$destination_path" ]]; then
        fail "odredisna putanja nije obicna datoteka: $relative_path"
    fi
done < <(git -C "$repo_root" ls-files -z)

while IFS= read -r -d '' relative_path; do
    is_protected_path "$relative_path" && continue

    source_path="$repo_root/$relative_path"
    destination_path="$target/$relative_path"
    relative_parent="${relative_path%/*}"

    if [[ "$relative_parent" == "$relative_path" ]]; then
        relative_parent='.'
        destination_parent="$target"
    else
        destination_parent="$target/$relative_parent"
    fi

    # Ova provjera mora biti prije mkdir kako symlink ne bi uzrokovao path escape.
    assert_safe_parent_path "$relative_parent"

    if [[ -f "$destination_path" ]] && /usr/bin/cmp -s -- "$source_path" "$destination_path"; then
        unchanged_count=$((unchanged_count + 1))
        continue
    fi

    if (( dry_run )); then
        printf 'KOPIRALO BI SE: %s\n' "$relative_path"
        copied_count=$((copied_count + 1))
        [[ -e "$destination_path" ]] && backup_count=$((backup_count + 1))
        continue
    fi

    /bin/mkdir -p -- "$destination_parent"

    # Ponovna provjera zatvara mogucnost da mkdir ili utrka uvedu symlink.
    assert_safe_parent_path "$relative_parent"
    destination_parent_real="$(cd "$destination_parent" && pwd -P)"

    case "$destination_parent_real/" in
        "$target/"*) ;;
        *) fail "odredisna putanja izlazi iz produkcijskog direktorija: $relative_path" ;;
    esac

    [[ ! -L "$destination_path" ]] || fail "odredisna datoteka je postala simbolicka poveznica: $relative_path"

    if [[ -e "$destination_path" ]]; then
        backup_path="$backup_root/$relative_path"
        /bin/mkdir -p -- "$(dirname "$backup_path")"
        /bin/cp -p -- "$destination_path" "$backup_path"
        backup_count=$((backup_count + 1))
    fi

    temporary_path="$destination_path.deploy-$deploy_id.tmp"
    [[ ! -e "$temporary_path" && ! -L "$temporary_path" ]] || fail "privremena datoteka vec postoji: $temporary_path"
    /bin/cp -p -- "$source_path" "$temporary_path"
    /bin/mv -f -- "$temporary_path" "$destination_path"
    copied_count=$((copied_count + 1))
done < <(git -C "$repo_root" ls-files -z)

if (( dry_run )); then
    printf 'Dry run dovrsen. Kopiralo bi se: %d; sigurnosno kopiralo: %d; nepromijenjeno: %d; zasticeno: %d.\n' \
        "$copied_count" "$backup_count" "$unchanged_count" "$protected_count"
else
    printf 'Deployment dovrsen. Kopirano: %d; sigurnosno kopirano: %d; nepromijenjeno: %d; zasticeno: %d.\n' \
        "$copied_count" "$backup_count" "$unchanged_count" "$protected_count"

    if (( backup_count > 0 )); then
        printf 'Sigurnosna kopija: %s\n' "$backup_root"
    fi
fi
