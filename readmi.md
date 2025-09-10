# Vacancy Importer

Plugin to import vacancies from the Arbeitnow API with a custom Gutenberg block and Contact Form 7 integration.

## Description
Vacancy Importer:
- Import vacancies via WP-CLI.
- Display vacancies via Gutenberg block with filters.
- Ability to integrate a Contact Form 7 application form.

## Installation
1. Upload the ZIP of the plugin.
2. In WordPress, go to **Plugins → Add New → Upload Plugin**.
3. Activate the plugin.
4. Ensure **Advanced Custom Fields** and **Contact Form 7** are active.
5. Perform the vacancies import (see "Import" section).

## Import
### WP-CLI Commands
- Default import (100 vacancies): `wp vacancies import`
- Import a specific number of vacancies: `wp vacancies import --count=150`
- Clear all imported vacancies and related taxonomies: `wp vacancies clear`

## Gutenberg Block
1. Add the **Vacancies List** block in the Gutenberg editor.
2. Configure the block:
   - **Per Page** — number of vacancies per page.
   - **Sort by** — sort by date, salary, or title.
   - **Location** — filter by city.
   - **Salary range** — filter by salary.
   - **Contact Form 7 shortcode** — application form shortcode.
3. Save the page or post — vacancies will appear on the frontend with filters and apply button.

## Contact Form 7
1. Create a form with ID (e.g., `123`) containing fields:
   - name
   - email
   - phone
   - message
   - hidden: vacancy_id
   - hidden: vacancy_title
2. Shortcode: `[contact-form-7 id="123" title="Vacancy Application"]`
3. Use this shortcode in the Gutenberg block settings.

## Changelog
### 1.0.0
* Initial release.
