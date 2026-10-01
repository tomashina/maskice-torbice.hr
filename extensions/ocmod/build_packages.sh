#!/usr/bin/env bash

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
OUTPUT_DIR="${1:-/Users/tomek/Desktop}"

CONTRACT_SOURCE="$SCRIPT_DIR/contract_withdrawal_basel_oc3"
GUARANTEE_SOURCE="$SCRIPT_DIR/legal_guarantee_basel_oc3"

CONTRACT_ZIP="$OUTPUT_DIR/agmedia_contract_withdrawal_basel_oc3_v1.2.0.ocmod.zip"
GUARANTEE_ZIP="$OUTPUT_DIR/agmedia_legal_guarantee_basel_oc3_v1.1.0.ocmod.zip"

WORK_DIR="$(mktemp -d "${TMPDIR:-/tmp}/agmedia-ocmod.XXXXXX")"
trap 'rm -rf "$WORK_DIR"' EXIT

mkdir -p "$OUTPUT_DIR"
rm -f "$CONTRACT_ZIP" "$GUARANTEE_ZIP"

build_base_archive() {
	local source_dir="$1"
	local archive="$2"
	local stage_dir="$3"

	mkdir -p "$stage_dir"
	cp -R "$source_dir/install.xml" "$source_dir/README.txt" "$source_dir/upload" "$stage_dir/"
	(
		cd "$stage_dir"
		COPYFILE_DISABLE=1 zip -X -q -r "$archive" install.xml README.txt upload
	)
}

build_base_archive "$CONTRACT_SOURCE" "$CONTRACT_ZIP" "$WORK_DIR/contract-base"
mkdir -p "$WORK_DIR/contract-lower/upload/admin/language/hr-hr/extension/sale"
mkdir -p "$WORK_DIR/contract-lower/upload/catalog/language/hr-hr/extension/account"
cp "$CONTRACT_SOURCE/upload/admin/language/hr-HR/extension/sale/contract_withdrawal.php" \
	"$WORK_DIR/contract-lower/upload/admin/language/hr-hr/extension/sale/contract_withdrawal.php"
cp "$CONTRACT_SOURCE/upload/catalog/language/hr-HR/extension/account/contract_withdrawal.php" \
	"$WORK_DIR/contract-lower/upload/catalog/language/hr-hr/extension/account/contract_withdrawal.php"
(
	cd "$WORK_DIR/contract-lower"
	COPYFILE_DISABLE=1 zip -X -q -r "$CONTRACT_ZIP" upload
)

build_base_archive "$GUARANTEE_SOURCE" "$GUARANTEE_ZIP" "$WORK_DIR/guarantee-base"
mkdir -p "$WORK_DIR/guarantee-upper/upload/catalog/language/hr-HR/extension/module"
cp "$GUARANTEE_SOURCE/upload/catalog/language/hr-hr/extension/module/legal_guarantee.php" \
	"$WORK_DIR/guarantee-upper/upload/catalog/language/hr-HR/extension/module/legal_guarantee.php"
(
	cd "$WORK_DIR/guarantee-upper"
	COPYFILE_DISABLE=1 zip -X -q -r "$GUARANTEE_ZIP" upload
)

for archive in "$CONTRACT_ZIP" "$GUARANTEE_ZIP"; do
	unzip -tq "$archive" >/dev/null
	if unzip -Z1 "$archive" | grep -Eq '(^|/)(__MACOSX|\.DS_Store)(/|$)|(^|/)install\.sql$'; then
		echo "Nedozvoljena datoteka u paketu: $archive" >&2
		exit 1
	fi
done

shasum -a 256 "$CONTRACT_ZIP" "$GUARANTEE_ZIP" > "$OUTPUT_DIR/agmedia_ocmod_SHA256SUMS.txt"

printf '%s\n' "$CONTRACT_ZIP" "$GUARANTEE_ZIP" "$OUTPUT_DIR/agmedia_ocmod_SHA256SUMS.txt"
