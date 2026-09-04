#!/usr/bin/env bash
# Verify Sep 4 FM ERP remediation files exist before/after deploy.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
MISSING=0

check() {
  local f="$1"
  if [[ -f "$ROOT/$f" && -s "$ROOT/$f" ]]; then
    echo "OK  $f"
  else
    echo "MISSING  $f"
    MISSING=$((MISSING + 1))
  fi
}

echo "=== FM ERP remediation file audit ==="
echo "Root: $ROOT"
echo

for f in \
  app/Controllers/PublicContractSign.php \
  app/Controllers/RemediationCheck.php \
  app/Services/ContractSignatureService.php \
  app/Services/SignatureStorageService.php \
  app/Services/UserFacilityService.php \
  app/Services/PropertyAssignmentService.php \
  app/Services/ParkingContractPhotoService.php \
  app/Views/partials/_lease_signature_panel.php \
  app/Views/public/contract_sign.php \
  public/assets/js/signature-pad.js \
  public/assets/css/contract-signature.css \
  public/BUILD.json \
  database/patches/2026-09-02-lease-contract-signature.sql \
  database/patches/2026-09-04-user-facilities-autoincrement.sql \
  database/patches/2026-09-04-parking-contract-photos.sql \
  database/patches/2026-09-04-user-landlord-link.sql \
  database/patches/fm-erp-complete.sql
do
  check "$f"
done

echo
if rg -q "generateSignLink|PublicContractSign::show|contract/sign" "$ROOT/app/Config/Routes.php" 2>/dev/null; then
  echo "OK  Routes.php (signature routes wired)"
else
  echo "MISSING  Routes.php signature routes"
  MISSING=$((MISSING + 1))
fi

if rg -q "fm_can_view_kpis" "$ROOT/app/Helpers/fm_helper.php" 2>/dev/null; then
  echo "OK  fm_helper.php (fm_can_view_kpis)"
else
  echo "MISSING  fm_can_view_kpis helper"
  MISSING=$((MISSING + 1))
fi

echo
if [[ $MISSING -eq 0 ]]; then
  echo "PASS — all remediation files present."
  exit 0
fi

echo "FAIL — $MISSING item(s) missing. Re-deploy from:"
echo "  git checkout cursor/fm-erp-remediation-a002"
exit 1
