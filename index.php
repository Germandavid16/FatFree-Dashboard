<?php 
// composer autoloader for required packages and dependencies
require_once('lib/autoload.php');

$f3 = \Base::instance();
ini_set('max_execution_time', 300);

require_once('config.php');

session_start();

$f3->route('GET @installDB: /installDB','Controllers\Main->actionInstallDB');
$f3->route('GET|POST @main: /','Controllers\Main->actionMain');
$f3->route('GET|POST @login: /login','Controllers\Main->actionUserLogin', 0, 64);
$f3->route('GET|POST @captcha: /captcha','Controllers\Main->actionCaptcha');
$f3->route('GET|POST @passwordRecovery: /recovery','Controllers\Main->actionPasswordRecovery');

$f3->route('GET|POST @roster: /roster','Controllers\Main->actionRoster');
$f3->route('GET|POST @rosterType: /roster/@type','Controllers\Main->actionRosterType');
$f3->route('GET|POST @rosterTypeYear: /roster/@type/@year','Controllers\Main->actionRosterTypeYear');
$f3->route('GET|POST @rosterTypeYearMonth: /roster/@type/@year/@month','Controllers\Main->actionRoster');
$f3->route('GET|POST @rosterTypeYearMonthDiff: /roster/@type/@year/@month/diff','Controllers\Main->actionRoster');
$f3->route('GET|POST @rosterTypeYearMonthGroup: /roster/@type/@year/@month/group','Controllers\Main->actionRoster');
$f3->route('GET|POST @rosterTypeYearMonthChart: /roster/@type/@year/@month/chart','Controllers\Main->actionRoster');

$f3->route('GET|POST @userPassword: /user/password','Controllers\Main->actionUserPassword');
$f3->route('GET|POST @userLogout: /user/logout','Controllers\Main->actionUserLogout');

$f3->route('GET|POST @adminFields: /admin/fields','Controllers\Main->actionAdminFields');
$f3->route('GET|POST @adminFieldsOne: /admin/fields/@id','Controllers\Main->actionAdminFieldsOne');
$f3->route('GET|POST @adminUsers: /admin/users','Controllers\Main->actionAdminUsers');

$f3->route('GET|POST @profile: /user/profile', 'Controllers\main->actionProfile');

$f3->route('GET|POST @adminUsersAdd: /admin/users/add','Controllers\Main->actionAdminUsersEdit');
$f3->route('GET|POST @adminUsersEdit: /admin/users/edit/@id','Controllers\Main->actionAdminUsersEdit');

$f3->route('GET|POST @adminInsurances: /insurances','Controllers\Main->actionAdminInsurances');
$f3->route('GET|POST @adminInsurancesAdd: /insurances/add','Controllers\Main->actionAdminInsurancesEdit');
$f3->route('GET|POST @adminInsurancesEdit: /insurances/edit/@id','Controllers\Main->actionAdminInsurancesEdit');

$f3->route('GET|POST @adminGroups: /admin/group','Controllers\Main->actionAdminGroups');
$f3->route('GET|POST @adminGroupsAdd: /admin/group/add','Controllers\Main->actionAdminGroupEdit');
$f3->route('GET|POST @adminGroupsEdit: /admin/group/edit/@id','Controllers\Main->actionAdminGroupEdit');
$session = new Session(NULL, 'CSRF');
$f3->sync('SESSION');
$view = \Classes\View::instance();
$view->extractSessionMessages();

$user = $f3->get('SESSION.user');
$menu = [];
if ($user) {
    $menu['@roster'] = ['title' => 'Roster',
        'submenu' => [
            'call' => 'Classes\Roster::menu',
            'params' => [$user['id']],
        ],
    ];
    $menu['@adminInsurances'] = ['title' => 'Insurances',
        'submenu' => [
            '@adminInsurancesAdd' => ['title' => 'New insurance'],
        ],
    ];
    $menu['@adminFields'] = ['title' => 'Fields'];
    $menu['@adminGroups'] = ['title' => 'Groups',
        'submenu' => [
            '@adminGroupsAdd' => ['title' => 'New Group'],
        ],
    ];
    $menu['@adminUsers'] = ['title' => 'Users',
        'submenu' => [
            '@adminUsersAdd' => ['title' => 'New user'],
        ],
    ];
    $menu['@profile'] = ['title' => "My Profile"];
    $menu['@userPassword'] = ['title' => 'Change password'];
    $menu['@userLogout'] = ['title' => 'Logout'];
}

if ($user['role'] != 'admin') {
    unset($menu['@adminInsurances']);
    unset($menu['@adminFields']);
    unset($menu['@adminUsers']);
    unset($menu['@adminGroups']);
} else {
    unset($menu['@profile']);
}

$obMenu = new \Classes\Menu($menu);
$menuList = $obMenu->getMenuList();
$f3->set('menu', $menuList[0]);

$submenuList = [];
for ($i = 1; $i < count($menuList); $i++) {
    $submenuList[$i] = $menuList[$i];
}

$base = "http://".$_SERVER['HTTP_HOST'];
$base .= str_replace(basename($_SERVER['SCRIPT_NAME']), "", $_SERVER['SCRIPT_NAME']);
$f3->set('base_url', substr($base, 0, -1));


$f3->set('submenuList', $submenuList);
$f3->run();
