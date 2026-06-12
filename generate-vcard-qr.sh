#!/usr/bin/env bash
set -euo pipefail

# ---------------------------------------------------------------------------
# generate-vcard-qr.sh — vCard 3.0 QR code generator (PNG + SVG)
# Dependencies: qrencode
# Optional:     imagemagick (for PNG quiet-zone padding)
# ---------------------------------------------------------------------------

usage() {
    cat <<EOF
Usage: $(basename "$0") [OPTIONS]

Required:
  -n, --name        First name
  -s, --surname     Last name

Optional:
  -o, --org         Organization / company
  -t, --tel         Phone number (e.g. +39 333 1234567)
  -e, --email       Email address
  -w, --web         Website URL
  -a, --address     Full address (street, city, country)
  -f, --file        Output file base name (default: firstname_lastname)
  -d, --dir         Output directory (default: current directory)
  -h, --help        Show this help

Examples:
  $(basename "$0") -n Mario -s Rossi -t "+39 333 1234567" -e mario@example.com
  $(basename "$0") -n Anna -s Bianchi -o "Acme Srl" -w https://acme.it -d ./output
EOF
    exit 0
}

FIRST_NAME=""
LAST_NAME=""
ORG=""
TEL=""
EMAIL=""
WEB=""
ADDRESS=""
OUT_FILE=""
OUT_DIR="."

while [[ $# -gt 0 ]]; do
    case "$1" in
        -n|--name)     FIRST_NAME="$2"; shift 2 ;;
        -s|--surname)  LAST_NAME="$2";  shift 2 ;;
        -o|--org)      ORG="$2";        shift 2 ;;
        -t|--tel)      TEL="$2";        shift 2 ;;
        -e|--email)    EMAIL="$2";      shift 2 ;;
        -w|--web)      WEB="$2";        shift 2 ;;
        -a|--address)  ADDRESS="$2";    shift 2 ;;
        -f|--file)     OUT_FILE="$2";   shift 2 ;;
        -d|--dir)      OUT_DIR="$2";    shift 2 ;;
        -h|--help)     usage ;;
        *) echo "Unknown option: $1" >&2; usage ;;
    esac
done

if [[ -z "$FIRST_NAME" || -z "$LAST_NAME" ]]; then
    echo "Error: --name and --surname are required." >&2
    echo "Run '$(basename "$0") --help' for usage." >&2
    exit 1
fi

if ! command -v qrencode &>/dev/null; then
    echo "Error: 'qrencode' is not installed." >&2
    echo "  Ubuntu/Debian: sudo apt install qrencode" >&2
    echo "  Fedora/RHEL:   sudo dnf install qrencode" >&2
    echo "  Arch:          sudo pacman -S qrencode" >&2
    exit 1
fi

mkdir -p "$OUT_DIR"

if [[ -z "$OUT_FILE" ]]; then
    OUT_FILE="${FIRST_NAME,,}_${LAST_NAME,,}"
    OUT_FILE="${OUT_FILE// /_}"
fi

PNG_OUT="${OUT_DIR}/${OUT_FILE}.png"
SVG_OUT="${OUT_DIR}/${OUT_FILE}.svg"

VCARD="BEGIN:VCARD"$'\n'
VCARD+="VERSION:3.0"$'\n'
VCARD+="N:${LAST_NAME};${FIRST_NAME};;;"$'\n'
VCARD+="FN:${FIRST_NAME} ${LAST_NAME}"$'\n'
[[ -n "$ORG"     ]] && VCARD+="ORG:${ORG}"$'\n'
[[ -n "$TEL"     ]] && VCARD+="TEL;TYPE=CELL:${TEL}"$'\n'
[[ -n "$EMAIL"   ]] && VCARD+="EMAIL;TYPE=INTERNET:${EMAIL}"$'\n'
[[ -n "$WEB"     ]] && VCARD+="URL:${WEB}"$'\n'
[[ -n "$ADDRESS" ]] && VCARD+="ADR;TYPE=WORK:;;${ADDRESS};;;;"$'\n'
VCARD+="END:VCARD"

# Error correction level M (15%) — good balance between data density and
# scan reliability on printed cards, even with minor damage or glare.
echo "$VCARD" | qrencode \
    --type=PNG \
    --level=M \
    --size=10 \
    --margin=4 \
    --output="$PNG_OUT"

if command -v convert &>/dev/null; then
    convert "$PNG_OUT" \
        -filter Point \
        -resize 1000x1000 \
        -background white \
        -gravity center \
        -extent 1000x1000 \
        "$PNG_OUT"
fi

echo "$VCARD" | qrencode \
    --type=SVG \
    --level=M \
    --margin=4 \
    --output="$SVG_OUT"

echo "vCard QR code generated:"
echo "  PNG: $PNG_OUT"
echo "  SVG: $SVG_OUT"
echo ""
echo "vCard payload:"
echo "$VCARD"
