This extension will help you work with the localization file system so that you do not have to manually edit folders and files.

```
<?php

declare(strict_types=1);

use Yarmoshuk\Localization\ManagerLocalization;

require_once __DIR__ . '/vendor/autoload.php';

$pathLocal = __DIR__ . DIRECTORY_SEPARATOR . 'lang';
$lang = 'en';
$section = 'messages';
$key = 'hello';
$value = 'Welcome';
$langNew = 'uk';
$sectionNew = 'auth';
$keyNew = 'key_new';

$managerLocalization = (new ManagerLocalization($pathLocal))->createLang($lang);
$managerLocalization = (new ManagerLocalization($pathLocal))->renameLang($langNew, $lang);
//$managerLocalization = (new ManagerLocalization($pathLocal))->deleteLang($langNew);

$managerLocalization = (new ManagerLocalization($pathLocal))->createSection($section);
$managerLocalization = (new ManagerLocalization($pathLocal))->renameSection($sectionNew, $section);
//$managerLocalization = (new ManagerLocalization($pathLocal))->deleteSection($key);

$managerLocalization = (new ManagerLocalization($pathLocal))->createKey($key, $sectionNew);
$managerLocalization = (new ManagerLocalization($pathLocal))->renameKey($keyNew, $key, $sectionNew);
$managerLocalization = (new ManagerLocalization($pathLocal))->setValueForKey($keyNew, $value, $sectionNew, $langNew);
$managerLocalization = (new ManagerLocalization($pathLocal))->getKeys($sectionNew);
$managerLocalization = (new ManagerLocalization($pathLocal))->getKey($keyNew, $sectionNew);
//$managerLocalization = (new ManagerLocalization($pathLocal))->deleteKey($keyNew, $sectionNew);
```
