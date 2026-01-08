#!/usr/bin/env bash

set -eu

# Print tutorials table header
# print_header
print_header() {
    cat <<EOF
| Date    | Path    | Title   | Author  |
| ------- | ------- | ------- | ------- |
EOF
}

# Print tutorial table row
# print_row <date> <slug> <title> <author>
print_row() {
    echo "| $1 | [$2](https://github.com/hetzneronline/community-content/blob/master/tutorials/$2/01.en.md) | $3 | $4 |"
}

# Create temporary files and add cleanup hook
rows="$(mktemp rows.XXXXXX.md)"
rows_orphaned="$(mktemp rows-orphaned.XXXXXX.md)"
trap 'rm -f "$rows" "$rows_orphaned"' EXIT

curr_year=$(date +'%Y')
curr_month=$(date +'%m')

# Run for each english tutorial file
for file in $(find ./tutorials -type f -name "*.en.md" | sort); do

    date=$(yq --front-matter extract '.date' "$file")

    # Get tutorial year and month
    tutor_year=$(date +'%Y' -d "$date")
    tutor_month=$(date +'%m' -d "$date")

    # Check if the tutorial date is older that 1 year ago and on the same month
    # Comparing numbers as strings, it's fine here
    if [[ "$tutor_month" == "$curr_month" && "$tutor_year" < "$curr_year" ]]; then

        slug=$(yq --front-matter extract '.slug' "$file")
        title=$(yq --front-matter extract '.title' "$file")
        author=$(yq --front-matter extract '.author_link' "$file" | sed 's|https://github.com/|@|')

        if [[ "$author" == '@hetzneronline' ]]; then
            author=Hetzner
        fi

        if [[ -z "$author" ]]; then
            print_row "$date" "$slug" "$title" "$author" >>"$rows_orphaned"
        else
            print_row "$date" "$slug" "$title" "$author" >>"$rows"
        fi
    fi
done

# Print summary header
cat <<EOF
The tutorials below were written over a year ago.

All authors, please do the following:

- Take a look at your tutorial and check if it still works.
- Comment on this issue if your tutorial needs an update or not.
- If your tutorial does need an update, please let us know if you plan to update the
  tutorial yourself. If not, we might need to delete your tutorial.

EOF

# Print table and sort rows by date
print_header
sort "$rows"
sort "$rows_orphaned"

# Print empty up to date table
cat <<EOF

---

Up to date:

EOF
print_header
