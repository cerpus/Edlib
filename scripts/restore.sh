#!/usr/bin/env bash
#
# restore.sh - Restore script for Edlib Hub and Content Author databases and filesystem
#

set -eo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"

# Default configurations
RESTORE_HUB=true
RESTORE_CA=true
RESTORE_DB=true
RESTORE_FILES=true
RESTORE_ENV=false
CONFIRM=true
VERBOSE=false
BACKUP_SOURCE=""

show_help() {
    cat <<EOF
Usage: $(basename "$0") [OPTIONS] [BACKUP_PATH]

Restores Edlib Hub and Content Author databases and filesystems from a backup.
Uses current .env files (or backup environment configurations) for database connections.

Options:
  -y, --yes, --no-confirm  Skip confirmation prompt before restoring.
  --hub-only               Only restore Hub (database and files).
  --ca-only,
  --contentauthor-only     Only restore Content Author (database and files).
  --db-only                Only restore databases.
  --files-only             Only restore filesystem storage.
  --restore-env            Also restore .env files from the backup.
  -v, --verbose            Enable verbose output.
  -h, --help               Show this help message and exit.

Arguments:
  BACKUP_PATH              Path to a backup directory or .tar.gz archive.
                           If not specified, the latest backup in ./backups/ is used.

Examples:
  $(basename "$0")
  $(basename "$0") -y backups/backup_20261012_085200.tar.gz
  $(basename "$0") --db-only /path/to/backup_dir
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
        -y|--yes|--no-confirm)
            CONFIRM=false
            shift
            ;;
        -v|--verbose)
            VERBOSE=true
            shift
            ;;
        --hub-only)
            RESTORE_HUB=true
            RESTORE_CA=false
            shift
            ;;
        --ca-only|--contentauthor-only)
            RESTORE_HUB=false
            RESTORE_CA=true
            shift
            ;;
        --db-only)
            RESTORE_DB=true
            RESTORE_FILES=false
            shift
            ;;
        --files-only)
            RESTORE_DB=false
            RESTORE_FILES=true
            shift
            ;;
        --restore-env)
            RESTORE_ENV=true
            shift
            ;;
        *)
            if [[ -z "$BACKUP_SOURCE" && ! "$1" =~ ^- ]]; then
                BACKUP_SOURCE="$1"
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

    local val="${line#*=}"
    val="$(echo "$val" | sed -e 's/^[[:space:]]*//' -e 's/[[:space:]]*$//')"

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

# Find backup source if not specified
if [ -z "$BACKUP_SOURCE" ]; then
    BACKUP_BASE_DIR="$PROJECT_ROOT/backups"
    if [ -d "$BACKUP_BASE_DIR" ]; then
        # Find latest file or directory in backups/
        LATEST_BACKUP=$(ls -td "$BACKUP_BASE_DIR"/* 2>/dev/null | head -n 1 || true)
        if [ -n "$LATEST_BACKUP" ]; then
            BACKUP_SOURCE="$LATEST_BACKUP"
            log "No backup specified. Using latest backup found: $BACKUP_SOURCE"
        fi
    fi
fi

if [ -z "$BACKUP_SOURCE" ] || [ ! -e "$BACKUP_SOURCE" ]; then
    error "Backup source not found or not specified: '$BACKUP_SOURCE'"
    exit 1
fi

TEMP_EXTRACT_DIR=""
cleanup() {
    if [ -n "$TEMP_EXTRACT_DIR" ] && [ -d "$TEMP_EXTRACT_DIR" ]; then
        rm -rf "$TEMP_EXTRACT_DIR"
    fi
}
trap cleanup EXIT

# If backup source is an archive, extract it
if [[ -f "$BACKUP_SOURCE" && ("$BACKUP_SOURCE" =~ \.tar\.gz$ || "$BACKUP_SOURCE" =~ \.tgz$) ]]; then
    TEMP_EXTRACT_DIR=$(mktemp -d -t edlib_restore_XXXXXX)
    log "Extracting backup archive '$BACKUP_SOURCE' to temporary directory..."
    tar -xzf "$BACKUP_SOURCE" -C "$TEMP_EXTRACT_DIR"
    RESTORE_DIR="$TEMP_EXTRACT_DIR"
elif [ -d "$BACKUP_SOURCE" ]; then
    RESTORE_DIR="$BACKUP_SOURCE"
else
    error "Backup path must be a directory or a .tar.gz archive: $BACKUP_SOURCE"
    exit 1
fi

# Confirmation prompt
if [ "$CONFIRM" = true ]; then
    echo -e "\033[1;33m[WARNING] This operation will overwrite existing database records and files!\033[0m"
    echo "Restore source: $BACKUP_SOURCE"
    read -p "Are you sure you want to proceed with restore? (y/N): " -r RESPONSE
    if [[ ! "$RESPONSE" =~ ^[Yy]$ ]]; then
        log "Restore operation cancelled by user."
        exit 0
    fi
fi

log "Starting restore process..."

# Determine .env files to use for credentials
HUB_ENV="$PROJECT_ROOT/sourcecode/hub/.env"
if [ ! -f "$HUB_ENV" ]; then
    if [ -f "$RESTORE_DIR/hub.env" ]; then
        HUB_ENV="$RESTORE_DIR/hub.env"
    elif [ -f "$PROJECT_ROOT/sourcecode/hub/.env.example" ]; then
        HUB_ENV="$PROJECT_ROOT/sourcecode/hub/.env.example"
    fi
fi

CA_ENV="$PROJECT_ROOT/sourcecode/apis/contentauthor/.env"
if [ ! -f "$CA_ENV" ]; then
    if [ -f "$RESTORE_DIR/ca.env" ]; then
        CA_ENV="$RESTORE_DIR/ca.env"
    elif [ -f "$PROJECT_ROOT/sourcecode/apis/contentauthor/.env.example" ]; then
        CA_ENV="$PROJECT_ROOT/sourcecode/apis/contentauthor/.env.example"
    fi
fi

# Optional: Restore .env files
if [ "$RESTORE_ENV" = true ]; then
    if [ -f "$RESTORE_DIR/hub.env" ]; then
        log "Restoring Hub .env file..."
        cp "$RESTORE_DIR/hub.env" "$PROJECT_ROOT/sourcecode/hub/.env"
    fi
    if [ -f "$RESTORE_DIR/ca.env" ]; then
        log "Restoring Content Author .env file..."
        cp "$RESTORE_DIR/ca.env" "$PROJECT_ROOT/sourcecode/apis/contentauthor/.env"
    fi
fi

# Function to restore postgres database
restore_postgres() {
    local host="$1"
    local port="$2"
    local db="$3"
    local user="$4"
    local pass="$5"
    local infile="$6"

    log "Restoring PostgreSQL database '${db}' from $infile to ${host}:${port}..."

    if command -v psql >/dev/null 2>&1; then
        if PGPASSWORD="$pass" psql -h "$host" -p "$port" -U "$user" -d "$db" -f "$infile" >/dev/null 2>&1; then
            log "Successfully restored PostgreSQL database via psql."
            return 0
        fi
        if [ "$host" != "127.0.0.1" ] && [ "$host" != "localhost" ]; then
            if PGPASSWORD="$pass" psql -h "127.0.0.1" -p "$port" -U "$user" -d "$db" -f "$infile" >/dev/null 2>&1; then
                log "Successfully restored PostgreSQL database via psql on 127.0.0.1."
                return 0
            fi
        fi
    fi

    # Docker fallback
    if command -v docker >/dev/null 2>&1; then
        local pg_container
        pg_container=$(docker ps --filter "name=postgres" --format "{{.ID}}" | head -n 1 || true)
        if [ -n "$pg_container" ]; then
            if docker exec -i "$pg_container" psql -U "$user" -d "$db" < "$infile" >/dev/null 2>&1; then
                log "Successfully restored PostgreSQL database via docker exec."
                return 0
            fi
        fi
    fi

    error "Failed to restore PostgreSQL database '${db}'."
    return 1
}

# Function to restore mysql database
restore_mysql() {
    local host="$1"
    local port="$2"
    local db="$3"
    local user="$4"
    local pass="$5"
    local infile="$6"

    log "Restoring MySQL/MariaDB database '${db}' from $infile to ${host}:${port}..."

    local client_cmd=""
    if command -v mariadb >/dev/null 2>&1; then
        client_cmd="mariadb"
    elif command -v mysql >/dev/null 2>&1; then
        client_cmd="mysql"
    fi

    if [ -n "$client_cmd" ]; then
        if MYSQL_PWD="$pass" $client_cmd -h "$host" -P "$port" -u "$user" "$db" < "$infile" >/dev/null 2>&1; then
            log "Successfully restored MySQL database via $client_cmd."
            return 0
        fi
        if [ "$host" != "127.0.0.1" ] && [ "$host" != "localhost" ]; then
            if MYSQL_PWD="$pass" $client_cmd -h "127.0.0.1" -P "$port" -u "$user" "$db" < "$infile" >/dev/null 2>&1; then
                log "Successfully restored MySQL database via $client_cmd on 127.0.0.1."
                return 0
            fi
        fi
    fi

    # Docker fallback
    if command -v docker >/dev/null 2>&1; then
        local mysql_container
        mysql_container=$(docker ps --filter "name=mysql" --format "{{.ID}}" | head -n 1 || true)
        if [ -n "$mysql_container" ]; then
            if docker exec -i "$mysql_container" mariadb -u "$user" -p"$pass" "$db" < "$infile" >/dev/null 2>&1 || \
               docker exec -i "$mysql_container" mysql -u "$user" -p"$pass" "$db" < "$infile" >/dev/null 2>&1; then
                log "Successfully restored MySQL database via docker exec."
                return 0
            fi
        fi
    fi

    error "Failed to restore MySQL database '${db}'."
    return 1
}

# 1. Restore Hub Database
if [ "$RESTORE_HUB" = true ] && [ "$RESTORE_DB" = true ]; then
    HUB_DUMP_FILE="$RESTORE_DIR/hub_database.sql"
    if [ -f "$HUB_DUMP_FILE" ]; then
        HUB_DB_CONN=$(parse_env_var "$HUB_ENV" "DB_CONNECTION" "pgsql")
        HUB_DB_HOST=$(parse_env_var "$HUB_ENV" "DB_HOST" "127.0.0.1")
        HUB_DB_PORT=$(parse_env_var "$HUB_ENV" "DB_PORT" "5432")
        HUB_DB_NAME=$(parse_env_var "$HUB_ENV" "DB_DATABASE" "hub")
        HUB_DB_USER=$(parse_env_var "$HUB_ENV" "DB_USERNAME" "postgres")
        HUB_DB_PASS=$(parse_env_var "$HUB_ENV" "DB_PASSWORD" "")

        log "Restoring Hub database (type=$HUB_DB_CONN db=$HUB_DB_NAME host=$HUB_DB_HOST:$HUB_DB_PORT)..."
        if [[ "$HUB_DB_CONN" =~ pgsql|postgres ]]; then
            restore_postgres "$HUB_DB_HOST" "$HUB_DB_PORT" "$HUB_DB_NAME" "$HUB_DB_USER" "$HUB_DB_PASS" "$HUB_DUMP_FILE"
        elif [[ "$HUB_DB_CONN" =~ mysql|mariadb ]]; then
            restore_mysql "$HUB_DB_HOST" "$HUB_DB_PORT" "$HUB_DB_NAME" "$HUB_DB_USER" "$HUB_DB_PASS" "$HUB_DUMP_FILE"
        fi
    else
        warn "Hub database dump not found in backup ($HUB_DUMP_FILE)"
    fi
fi

# 2. Restore Content Author Database
if [ "$RESTORE_CA" = true ] && [ "$RESTORE_DB" = true ]; then
    CA_DUMP_FILE="$RESTORE_DIR/ca_database.sql"
    if [ -f "$CA_DUMP_FILE" ]; then
        CA_DB_CONN=$(parse_env_var "$CA_ENV" "DB_CONNECTION" "mysql")
        CA_DB_HOST=$(parse_env_var "$CA_ENV" "DB_HOST" "127.0.0.1")
        CA_DB_PORT=$(parse_env_var "$CA_ENV" "DB_PORT" "3306")
        CA_DB_NAME=$(parse_env_var "$CA_ENV" "DB_DATABASE" "edlibcontentauthor")
        CA_DB_USER=$(parse_env_var "$CA_ENV" "DB_USERNAME" "root")
        CA_DB_PASS=$(parse_env_var "$CA_ENV" "DB_PASSWORD" "")

        log "Restoring Content Author database (type=$CA_DB_CONN db=$CA_DB_NAME host=$CA_DB_HOST:$CA_DB_PORT)..."
        if [[ "$CA_DB_CONN" =~ mysql|mariadb ]]; then
            restore_mysql "$CA_DB_HOST" "$CA_DB_PORT" "$CA_DB_NAME" "$CA_DB_USER" "$CA_DB_PASS" "$CA_DUMP_FILE"
        elif [[ "$CA_DB_CONN" =~ pgsql|postgres ]]; then
            restore_postgres "$CA_DB_HOST" "$CA_DB_PORT" "$CA_DB_NAME" "$CA_DB_USER" "$CA_DB_PASS" "$CA_DUMP_FILE"
        fi
    else
        warn "Content Author database dump not found in backup ($CA_DUMP_FILE)"
    fi
fi

# 3. Restore Hub Filesystem
if [ "$RESTORE_HUB" = true ] && [ "$RESTORE_FILES" = true ]; then
    HUB_STORAGE_ARCHIVE="$RESTORE_DIR/hub_storage.tar.gz"
    HUB_TARGET_DIR="$PROJECT_ROOT/sourcecode/hub/storage/app"
    if [ -f "$HUB_STORAGE_ARCHIVE" ]; then
        log "Restoring Hub storage to $HUB_TARGET_DIR..."
        mkdir -p "$HUB_TARGET_DIR"
        tar -xzf "$HUB_STORAGE_ARCHIVE" -C "$HUB_TARGET_DIR" --no-overwrite-dir --no-same-owner --no-same-permissions
        log "Hub storage files restored successfully."
    else
        warn "Hub storage archive not found ($HUB_STORAGE_ARCHIVE)"
    fi
fi

# 4. Restore Content Author Filesystem
if [ "$RESTORE_CA" = true ] && [ "$RESTORE_FILES" = true ]; then
    CA_STORAGE_ARCHIVE="$RESTORE_DIR/ca_storage.tar.gz"
    CA_TARGET_DIR="$PROJECT_ROOT/sourcecode/apis/contentauthor/storage/app"
    if [ -f "$CA_STORAGE_ARCHIVE" ]; then
        log "Restoring Content Author storage to $CA_TARGET_DIR..."
        mkdir -p "$CA_TARGET_DIR"
        tar -xzf "$CA_STORAGE_ARCHIVE" -C "$CA_TARGET_DIR" --no-overwrite-dir --no-same-owner --no-same-permissions
        log "Content Author storage files restored successfully."
    else
        warn "Content Author storage archive not found ($CA_STORAGE_ARCHIVE)"
    fi
fi

log "=================================================="
log "Restore completed successfully!"
log "=================================================="
