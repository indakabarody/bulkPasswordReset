# Bulk Password Reset Plugin for OJS 3.3

**Bulk Password Reset** is a generic plugin for Open Journal Systems (OJS) 3.3 designed to allow Journal Managers to reset passwords in bulk for all users in a specific role (e.g., Reviewers, Authors, Editors) within a single journal context.

The plugin provides advanced features like specific user selection, forced password change on next login, email notification, and a secure one-time CSV export.

---

## Key Features

- **Context-Specific Scope**: Operates strictly within the current journal context. Users with roles in other journals remain unaffected unless explicitly chosen.
- **Role-Based Targeting**: Easily target specific groups such as Journal Managers, Editors, Section Editors, Reviewers, Authors, Reader groups, **All Roles in the Journal**, or **All Roles in the entire OJS installation**.
- **User Selection**: Option to select specific users within the chosen role to reset passwords. If the user count exceeds 500, this feature is automatically disabled to ensure performance.
- **Customizable Password Generation**:
  - Set custom password lengths (minimum 8 characters).
  - Choose character pools: Uppercase letters, lowercase letters, numbers, and symbols.
- **Optional Forced Password Reset**: Toggle whether users must change their password on their next login.
- **Optional Direct Email Notification**: Send the new password to the user's email address immediately. The email includes proper formatting, clickable login links, and is signed by the journal's Principal Contact.
- **One-Time CSV Export**:
  - Generates a downloadable CSV containing `Username`, `Email`, `First/Last Name`, and the `New Password` (in plain text).
  - **Single Download Enforcement**: The CSV file is deleted from the server immediately after download, and the download token is invalidated.
- **Multi-language Support**: Fully localized with `.po` files for English (US) and Indonesian (id_ID).

---

## Security Considerations

> [!WARNING]
> **Global Account Impact**: OJS user accounts are global across the entire installation. If you reset the password for a user who also has roles in *other* journals on the same OJS installation, their password will be changed for those journals as well. They must use the newly generated password to log into all journals.

> [!IMPORTANT]
> Because this tool resets passwords in bulk, please review these safety mechanisms:
> - **Plain-text passwords** are never saved permanently in the OJS database or logs. Only the standard cryptographic password hashes are stored.
> - **Download only once**: The CSV file containing the plain-text passwords is saved to the system's temporary directory (`sys_get_temp_dir()`) and is tied to the current user's session. As soon as the file is downloaded or the session expires, the file is permanently deleted from the disk.
> - **Forced Change**: If enabled, users cannot continue using the generated passwords indefinitely; OJS will prompt them to change it immediately upon login.

---

## Installation

### Method 1: Upload via OJS Administrator Panel (Recommended)
1. Compress the plugin directory into a `.tar.gz` format (e.g., `bulkPasswordReset.tar.gz`).
2. Log in to OJS as a **Site Administrator**.
3. Navigate to **Administration** > **Site Settings** > **Plugins** (or **Settings** > **Website** > **Plugins**).
4. Click **Upload A New Plugin**.
5. Upload your `.tar.gz` file and confirm.

### Method 2: Manual Installation
1. Upload/copy the `bulkPasswordReset` directory to your OJS installation directory under `plugins/generic/`:
   ```bash
   cp -r bulkPasswordReset /path/to/your/ojs/plugins/generic/
   ```
2. Make sure the folder permissions match your web server user (e.g. `www-data` or `apache`).
3. Log in as a **Journal Manager** or **Site Administrator**.
4. Go to **Settings** > **Website** > **Plugins** > **Installed Plugins**.
5. Find **Bulk Password Reset** in the **Generic Plugins** list and check the checkbox to enable it.

---

## How to Use

1. Navigate to **Settings** > **Website** > **Plugins**.
2. Expand the **Bulk Password Reset** generic plugin using the blue disclosure arrow.
3. Click **Bulk Reset Tool** (Settings).
4. **Step 1: Configuration**
   - Select the target user role (e.g., specific roles, all journal roles, or all OJS roles).
   - Choose your password length and character requirements.
   - Choose additional options: **Force users to change their password on their next login** and/or **Send the new password to the user's email address immediately**.
   - Click **Next**.
5. **Step 2: Preview & Confirmation**
   - Review the warning detailing the count of affected users.
   - You can uncheck specific users if you do not wish to reset their password (available if the list contains 500 or fewer users).
   - Check the **"Yes, I understand and want to continue."** box.
   - Click **Execute Reset**.
6. **Step 3: Export Download**
   - Once successfully processed, click **Download CSV Export**.
   - *Note: Please save the CSV file in a secure location. You cannot download this file again.*

---

## Specifications

- **Plugin Type**: Generic Plugin (`plugins.generic`)
- **Compatibility**: OJS 3.3.x
- **Author**: Indaka Barody
- **Copyright**: Copyright (c) 2026 Indaka Barody
- **License**: GNU GPL v3
