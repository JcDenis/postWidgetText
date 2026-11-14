# postWidgetText

Notes for AI assistants working on the Dotclear plugin postWidgetText.

**Read first** the shared conventions of the author's Dotclear modules:
https://raw.githubusercontent.com/JcDenis/gh2da/master/CONVENTIONS.md
(if a web tool refuses the address, use `curl -s` on it from the shell).
The rules below take precedence for this module.

## Module

Add a widget related to an entry with custom titles and content. A [user guide in French](https://github.com/JcDenis/postWidgetText/blob/master/docs/fr/utilisation.md) is available.

Admin page. Public side: behaviors.

Required Dotclear and PHP versions: see `_define.php`.

## Structure

* `src/Backend.php`: admin side: menu entry, favorites, behaviors registration.
* `src/BackendBehaviors.php`: admin behaviors.
* `src/Frontend.php`: public side: behaviors, template tags and URL handlers registration.
* `src/ImportExport.php`: plugin importExport stuff.
* `src/Install.php`: install and update, database structure (a change needs a migration here).
* `src/Manage.php`: admin page of the module.
* `src/ManageList.php`: admin page (list).
* `src/My.php`: module helpers (`My::id()`, settings, permissions).
* `src/Uninstall.php`: cleaners for the plugin Uninstaller.
* `src/Utils.php`: helpers.
* `src/Widgets.php`: widgets definition and rendering.
* `js/`: `backend.js`, `manage.js`.
* Database table `post_option`.
* Behaviors used: `initWidgets`, `adminFiltersListsV2`, `adminColumnsListsV2`, `adminBlogPreferencesFormV2`, `adminBeforeBlogSettingsUpdate`, `adminPostHeaders`, `adminPostEditorTags`, `adminPostFormItems`, `adminAfterPostUpdate`, `adminAfterPostCreate`, `adminBeforePostDelete`, `adminPageHeaders`, `adminPageFormItems`, `adminAfterPageUpdate`, `adminAfterPageCreate`, `adminBeforePageDelete`, `exportFullV2`, `exportSingleV2`, `importInitV2`, `importSingleV2`, `importFullV2`.

## Pitfalls

* Table structure changes need a migration in `src/Install.php` (version compare), and must work on MySQL, PostgreSQL and SQLite.

## Testing

* Check the admin page, the admin help, in English and French.
* CI browses the common admin and public pages; extra pages can be listed in `.github/dotclear-test.txt`.
* The user guide `docs/fr/utilisation.md` describes the features: update it, and its screenshots, when the interface changes.
