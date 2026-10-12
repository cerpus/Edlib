#!/usr/bin/env bash
#
# backup.sh - Backup script for Edlib Hub and Content Author databases and filesystem
#

set -eo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"

# Default configurations
BACKUP_HUB=true
BACKUP_CA=true
BACKUP_DB=true
BACKUP_FILES=true
CREATE_ARCHIVE=true
OUTPUT_TARGET=""
VERBOSE=false

show_help() {
    cat <<EOF
Usage: $(basename "$0") [OPTIONS] [OUTPUT_DIRECTORY]

Backs up Edlib Hub and Content Author databases and filesystems based on .env configurations.

Options:
  -o, --output DIR|FILE    Specify destination backup directory or .tar.gz archive path.
  --hub-only               Only back up Hub (database and files).
  --ca-only,
  --contentauthor-only     Only back up Content Author (database and files).
  --db-only                Only back up databases.
  --files-only             Only back up filesystem storage.
  --no-archive             Do not create a compressed .tar.gz archive (keep backup as directory).
  -v, --verbose            Enable verbose output.
  -h, --help               Show this help message and exit.

Examples:
  $(basename "$0")
  $(basename "$0") -o /var/backups/edlib_backup.tar.gz
  $(basename "$0") --db-only
  $(basename "$0") --hub-only
EOF
}

# Parse command-line arguments
while [[ $# -gt 0 ]]; do
    case "$1" in
        -h|--help)
            show_help
            exit 0
            ;;
        -v|--verbose)
            VERBOSE=true
            shift
            ;;
        -o|--output)
            OUTPUT_TARGET="$2"
            shift 2
            ;;
        --hub-only)
            BACKUP_HUB=true
            BACKUP_CA=false
            shift
            ;;
        --ca-only|--contentauthor-only)
            BACKUP_HUB=false
            BACKUP_CA=true
            shift
            ;;
        --db-only)
            BACKUP_DB=true
            BACKUP_FILES=false
            shift
            ;;
        --files-only)
            BACKUP_DB=false
            BACKUP_FILES=true
            shift
            ;;
        --no-archive)
            CREATE_ARCHIVE=false
            shift
            ;;
        *)
            if [[ -z "$OUTPUT_TARGET" && ! "$1" =~ ^- ]]; then
                OUTPUT_TARGET="$1"
                shift
            else
                echo "Unknown option: $1" >&2
                show_help
                exit 1
            fi
            ;;
    esac
done

log() {
    echo -e "\033[1;32m[INFO]\033[0m $*"
}

warn() {
    echo -e "\033[1;33m[WARN]\033[0m $*" >&2
}

error() {
    echo -e "\033[1;31m[ERROR]\033[0m $*" >&2
}

# Helper function to parse variable from .env file
parse_env_var() {
    local env_file="$1"
    local var_name="$2"
    local default_val="${3:-}"

    if [ ! -f "$env_file" ]; then
        echo "$default_val"
        return 0
    fi

    local line
    line=$(grep -E "^[[:space:]]*(export[[:space:]]+)?${var_name}[[:space:]]*=" "$env_file" 2>/dev/null | tail -n 1 || true)
    if [ -z "$line" ]; then
        echo "$default_val"
        return 0
    fi

    # Extract value after first '='
    local val="${line#*=}"
    val="$(echo "$val" | sed -e 's/^[[:space:]]*//' -e 's/[[:space:]]*$//')"

    # Handle quotes
    if [[ "$val" =~ ^\'.*\'$ ]]; then
        val="${val:1:-1}"
    elif [[ "$val" =~ ^\".*\"$ ]]; then
        val="${val:1:-1}"
    else
        val="$(echo "$val" | sed -e 's/[[:space:]]*#.*$//')"
    fi

    val="$(echo "$val" | sed -e 's/^[[:space:]]*//' -e 's/[[:space:]]*$//')"

    if [ -z "$val" ]; then
        echo "$default_val"
    else
        echo "$val"
    fi
}

TIMESTAMP="$(date +"%Y%m%d_%H%M%S")"
DEFAULT_BACKUP_NAME="backup_${TIMESTAMP}"
BACKUP_BASE_DIR="$PROJECT_ROOT/backups"

IS_ARCHIVE_TARGET=false
FINAL_ARCHIVE_PATH=""

if [ -n "$OUTPUT_TARGET" ]; then
    if [[ "$OUTPUT_TARGET" =~ \.tar\.gz$ || "$OUTPUT_TARGET" =~ \.tgz$ ]]; then
        IS_ARCHIVE_TARGET=true
        FINAL_ARCHIVE_PATH="$OUTPUT_TARGET"
        BACKUP_DIR=$(mktemp -d -t edlib_backup_XXXXXX)
        CLEANUP_TEMP_DIR=true
    else
        BACKUP_DIR="$OUTPUT_TARGET"
        CLEANUP_TEMP_DIR=false
    fi
else
    BACKUP_DIR="$BACKUP_BASE_DIR/$DEFAULT_BACKUP_NAME"
    CLEANUP_TEMP_DIR=false
fi

mkdir -p "$BACKUP_DIR"
if [ "$CREATE_ARCHIVE" = true ] && [ "$IS_ARCHIVE_TARGET" = false ]; then
    mkdir -p "$BACKUP_BASE_DIR"
    FINAL_ARCHIVE_PATH="$BACKUP_BASE_DIR/${DEFAULT_BACKUP_NAME}.tar.gz"
fi

cleanup() {
    if [ "${CLEANUP_TEMP_DIR:-false}" = true ] && [ -d "$BACKUP_DIR" ]; then
        rm -rf "$BACKUP_DIR"
    fi
}
trap cleanup EXIT

log "Starting Edlib backup process..."
log "Backup working directory: $BACKUP_DIR"

# Locate .env files
HUB_ENV="$PROJECT_ROOT/sourcecode/hub/.env"
CA_ENV="$PROJECT_ROOT/sourcecode/apis/contentauthor/.env"

if [ ! -f "$HUB_ENV" ] && [ -f "$PROJECT_ROOT/sourcecode/hub/.env.example" ]; then
    warn "Hub .env not found, using .env.example"
    HUB_ENV="$PROJECT_ROOT/sourcecode/hub/.env.example"
fi

if [ ! -f "$CA_ENV" ] && [ -f "$PROJECT_ROOT/sourcecode/apis/contentauthor/.env.example" ]; then
    warn "Content Author .env not found, using .env.example"
    CA_ENV="$PROJECT_ROOT/sourcecode/apis/contentauthor/.env.example"
fi

# Function to dump postgres database
dump_postgres() {
    local host="$1"
    local port="$2"
    local db="$3"
    local user="$4"
    local pass="$5"
    local outfile="$6"

    log "Dumping PostgreSQL database '${db}' from ${host}:${port}..."

    # Method 1: Local pg_dump CLI
    if command -v pg_dump >/dev/null 2>&1; then
        # Try configured host first
        if PGPASSWORD="$pass" pg_dump -h "$host" -p "$port" -U "$user" --clean --if-exists "$db" > "$outfile" 2>/dev/null; then
            log "Successfully dumped PostgreSQL database via pg_dump ($outfile)"
            return 0
        fi
        # If host failed (e.g. docker host name not resolving), try 127.0.0.1
        if [ "$host" != "127.0.0.1" ] && [ "$host" != "localhost" ]; then
            if PGPASSWORD="$pass" pg_dump -h "127.0.0.1" -p "$port" -U "$user" --clean --if-exists "$db" > "$outfile" 2>/dev/null; then
                log "Successfully dumped PostgreSQL database via pg_dump on 127.0.0.1 ($outfile)"
                return 0
            fi
        fi
    fi

    # Method 2: Docker compose exec
    if command -v docker >/dev/null 2>&1; then
        local pg_container
        pg_container=$(docker ps --filter "name=postgres" --format "{{.ID}}" | head -n 1 || true)
        if [ -n "$pg_container" ]; then
            if docker exec -i "$pg_container" pg_dump -U "$user" --clean --if-exists "$db" > "$outfile" 2>/dev/null; then
                log "Successfully dumped PostgreSQL database via docker exec ($outfile)"
                return 0
            fi
        fi
    fi

    error "Failed to dump PostgreSQL database '${db}'. Please check credentials and connectivity."
    return 1
}

# Function to dump mysql database
dump_mysql() {
    local host="$1"
    local port="$2"
    local db="$3"
    local user="$4"
    local pass="$5"
    local outfile="$6"

    log "Dumping MySQL/MariaDB database '${db}' from ${host}:${port}..."

    # Check for client tools: mariadb-dump or mysqldump
    local dump_cmd=""
    if command -v mariadb-dump >/dev/null 2>&1; then
        dump_cmd="mariadb-dump"
    elif command -v mysqldump >/dev/null 2>&1; then
        dump_cmd="mysqldump --column-statistics=0"
    fi

    if [ -n "$dump_cmd" ]; then
        if MYSQL_PWD="$pass" $dump_cmd -h "$host" -P "$port" -u "$user" --add-drop-table "$db" > "$outfile" 2>/dev/null; then
            log "Successfully dumped MySQL database via $dump_cmd ($outfile)"
            return 0
        fi
        if [ "$host" != "127.0.0.1" ] && [ "$host" != "localhost" ]; then
            if MYSQL_PWD="$pass" $dump_cmd -h "127.0.0.1" -P "$port" -u "$user" --add-drop-table "$db" > "$outfile" 2>/dev/null; then
                log "Successfully dumped MySQL database via $dump_cmd on 127.0.0.1 ($outfile)"
                return 0
            fi
        fi
    fi

    # Method 2: Docker compose exec
    if command -v docker >/dev/null 2>&1; then
        local mysql_container
        mysql_container=$(docker ps --filter "name=mysql" --format "{{.ID}}" | head -n 1 || true)
        if [ -n "$mysql_container" ]; then
            if docker exec -i "$mysql_container" mariadb-dump -u "$user" -p"$pass" --add-drop-table "$db" > "$outfile" 2>/dev/null || \
               docker exec -i "$mysql_container" mysqldump -u "$user" -p"$pass" --add-drop-table "$db" > "$outfile" 2>/dev/null; then
                log "Successfully dumped MySQL database via docker exec ($outfile)"
                return 0
            fi
        fi
    fi

    error "Failed to dump MySQL database '${db}'. Please check credentials and connectivity."
    return 1
}

# 1. Back up Hub Database
if [ "$BACKUP_HUB" = true ] && [ "$BACKUP_DB" = true ]; then
    if [ -f "$HUB_ENV" ]; then
        HUB_DB_CONN=$(parse_env_var "$HUB_ENV" "DB_CONNECTION" "pgsql")
        HUB_DB_HOST=$(parse_env_var "$HUB_ENV" "DB_HOST" "127.0.0.1")
        HUB_DB_PORT=$(parse_env_var "$HUB_ENV" "DB_PORT" "5432")
        HUB_DB_NAME=$(parse_env_var "$HUB_ENV" "DB_DATABASE" "hub")
        HUB_DB_USER=$(parse_env_var "$HUB_ENV" "DB_USERNAME" "postgres")
        HUB_DB_PASS=$(parse_env_var "$HUB_ENV" "DB_PASSWORD" "")

        log "Found Hub DB config: type=$HUB_DB_CONN host=$HUB_DB_HOST port=$HUB_DB_PORT db=$HUB_DB_NAME user=$HUB_DB_USER"
        if [[ "$HUB_DB_CONN" =~ pgsql|postgres ]]; then
            dump_postgres "$HUB_DB_HOST" "$HUB_DB_PORT" "$HUB_DB_NAME" "$HUB_DB_USER" "$HUB_DB_PASS" "$BACKUP_DIR/hub_database.sql"
        elif [[ "$HUB_DB_CONN" =~ mysql|mariadb ]]; then
            dump_mysql "$HUB_DB_HOST" "$HUB_DB_PORT" "$HUB_DB_NAME" "$HUB_DB_USER" "$HUB_DB_PASS" "$BACKUP_DIR/hub_database.sql"
        else
            error "Unsupported DB_CONNECTION '$HUB_DB_CONN' for Hub"
        fi
    else
        warn "Skipping Hub DB backup: no .env file found"
    fi
fi

# 2. Back up Content Author Database
if [ "$BACKUP_CA" = true ] && [ "$BACKUP_DB" = true ]; then
    if [ -f "$CA_ENV" ]; then
        CA_DB_CONN=$(parse_env_var "$CA_ENV" "DB_CONNECTION" "mysql")
        CA_DB_HOST=$(parse_env_var "$CA_ENV" "DB_HOST" "127.0.0.1")
        CA_DB_PORT=$(parse_env_var "$CA_ENV" "DB_PORT" "3306")
        CA_DB_NAME=$(parse_env_var "$CA_ENV" "DB_DATABASE" "edlibcontentauthor")
        CA_DB_USER=$(parse_env_var "$CA_ENV" "DB_USERNAME" "root")
        CA_DB_PASS=$(parse_env_var "$CA_ENV" "DB_PASSWORD" "")

        log "Found Content Author DB config: type=$CA_DB_CONN host=$CA_DB_HOST port=$CA_DB_PORT db=$CA_DB_NAME user=$CA_DB_USER"
        if [[ "$CA_DB_CONN" =~ mysql|mariadb ]]; then
            dump_mysql "$CA_DB_HOST" "$CA_DB_PORT" "$CA_DB_NAME" "$CA_DB_USER" "$CA_DB_PASS" "$BACKUP_DIR/ca_database.sql"
        elif [[ "$CA_DB_CONN" =~ pgsql|postgres ]]; then
            dump_postgres "$CA_DB_HOST" "$CA_DB_PORT" "$CA_DB_NAME" "$CA_DB_USER" "$CA_DB_PASS" "$BACKUP_DIR/ca_database.sql"
        else
            error "Unsupported DB_CONNECTION '$CA_DB_CONN' for Content Author"
        fi
    else
        warn "Skipping Content Author DB backup: no .env file found"
    fi
fi

# 3. Back up Hub Filesystem
if [ "$BACKUP_HUB" = true ] && [ "$BACKUP_FILES" = true ]; then
    HUB_STORAGE_DIR="$PROJECT_ROOT/sourcecode/hub/storage/app"
    HUB_FS_DISK=$(parse_env_var "$HUB_ENV" "FILESYSTEM_DISK" "local")
    log "Hub filesystem disk: $HUB_FS_DISK (storage: $HUB_STORAGE_DIR)"
    if [ -d "$HUB_STORAGE_DIR" ]; then
        log "Archiving Hub storage ($HUB_STORAGE_DIR)..."
        tar -czf "$BACKUP_DIR/hub_storage.tar.gz" -C "$HUB_STORAGE_DIR" .
        log "Hub storage archived successfully ($BACKUP_DIR/hub_storage.tar.gz)"
    else
        warn "Hub storage directory not found at $HUB_STORAGE_DIR, creating empty archive placeholder"
        tar -czf "$BACKUP_DIR/hub_storage.tar.gz" --files-from /dev/null
    fi
fi

# 4. Back up Content Author Filesystem
if [ "$BACKUP_CA" = true ] && [ "$BACKUP_FILES" = true ]; then
    CA_STORAGE_DIR="$PROJECT_ROOT/sourcecode/apis/contentauthor/storage/app"
    CA_FS_DRIVER=$(parse_env_var "$CA_ENV" "FILESYSTEM_DRIVER" "$(parse_env_var "$CA_ENV" "FILESYSTEM_DISK" "local")")
    log "Content Author filesystem driver: $CA_FS_DRIVER (storage: $CA_STORAGE_DIR)"
    if [ -d "$CA_STORAGE_DIR" ]; then
        log "Archiving Content Author storage ($CA_STORAGE_DIR)..."
        tar -czf "$BACKUP_DIR/ca_storage.tar.gz" -C "$CA_STORAGE_DIR" .
        log "Content Author storage archived successfully ($BACKUP_DIR/ca_storage.tar.gz)"
    else
        warn "Content Author storage directory not found at $CA_STORAGE_DIR, creating empty archive placeholder"
        tar -czf "$BACKUP_DIR/ca_storage.tar.gz" --files-from /dev/null
    fi
fi

# 5. Copy .env configurations for reference and safe restore
if [ -f "$HUB_ENV" ] && [ "$BACKUP_HUB" = true ]; then
    cp "$HUB_ENV" "$BACKUP_DIR/hub.env"
fi
if [ -f "$CA_ENV" ] && [ "$BACKUP_CA" = true ]; then
    cp "$CA_ENV" "$BACKUP_DIR/ca.env"
fi

# 6. Create backup manifest
cat <<EOF > "$BACKUP_DIR/manifest.json"
{
  "timestamp": "$TIMESTAMP",
  "created_at": "$(date -u +"%Y-%m-%dT%H:%M:%SZ")",
  "backup_hub": $BACKUP_HUB,
  "backup_ca": $BACKUP_CA,
  "backup_db": $BACKUP_DB,
  "backup_files": $BACKUP_FILES,
  "hub_db_name": "${HUB_DB_NAME:-}",
  "ca_db_name": "${CA_DB_NAME:-}"
}
EOF

# 7. Compress into archive if requested
if [ -n "$FINAL_ARCHIVE_PATH" ]; then
    log "Compressing backup into archive '$FINAL_ARCHIVE_PATH'..."
    mkdir -p "$(dirname "$FINAL_ARCHIVE_PATH")"
    tar -czf "$FINAL_ARCHIVE_PATH" -C "$BACKUP_DIR" .
    log "Backup archive created: $FINAL_ARCHIVE_PATH ($(du -sh "$FINAL_ARCHIVE_PATH" | cut -f1))"
fi

log "=================================================="
log "Backup completed successfully!"
if [ -n "$FINAL_ARCHIVE_PATH" ]; then
    log "Backup archive: $FINAL_ARCHIVE_PATH"
fi
if [ "$IS_ARCHIVE_TARGET" = false ]; then
    log "Backup directory: $BACKUP_DIR"
fi
log "=================================================="
