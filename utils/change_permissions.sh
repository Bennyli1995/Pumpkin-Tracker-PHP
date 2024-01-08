#!/bin/bash

# Change permissions for files of interest
# Instructions:
# Make the script executable:
#   chmod +x change_permissions.sh
# Run the script:
#   ./change_permissions.sh

# Make sure you always do this first:
# chmod 711 ~/public_html
# chmod 711 ~/public_html/project_a2v5h_e0p8y_y7v1z
# or else it will not work

# Define the root directory (assuming the script is run from inside public_html)
root_dir="."

# Define the directories
pages_dir="$root_dir/pages"
css_dir="$root_dir/css"
image_dir="$root_dir/images"

# Change directory permissions to 711
chmod 711 "$pages_dir"
chmod 711 "$css_dir"
chmod 711 "$image_dir"

# Change file permissions inside pages folder to 711
find "$pages_dir" -type f -exec chmod 711 {} \;

# Change file permissions inside css folder to 755
find "$css_dir" -type f -exec chmod 755 {} \;

# Change file permissions inside image folder to 644
find "$image_dir" -type f -exec chmod 644 {} \;

echo "Permissions updated successfully."
