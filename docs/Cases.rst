.. _Cases:

Real Code Cases
---------------

Introduction
############

All the examples in this section are real code, extracted from major PHP applications. 


Examples
########

Adding Zero
===========

.. _thelia-structures-addzero:

Thelia
^^^^^^

:ref:`adding-zero`, in /core/lib/Thelia/Model/Map/ProfileResourceTableMap.php:250. 

This return statement is doing quite a lot, including a buried '0 + $offset'. This call is probably an echo to '1 + $offset', which is a little later in the expression.

.. code-block:: php

    return serialize(array((string) $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('ProfileId', TableMap::TYPE_PHPNAME, $indexType)], (string) $row[TableMap::TYPE_NUM == $indexType ? 1 + $offset : static::translateFieldName('ResourceId', TableMap::TYPE_PHPNAME, $indexType)]));

--------



.. _openemr-structures-addzero:

OpenEMR
^^^^^^^

:ref:`adding-zero`, in /interface/forms/fee_sheet/new.php:466:534. 

$main_provid is filtered as an integer. $main_supid is then filtered twice : one with the sufficent (int) and then, added with 0.

.. code-block:: php

    if (!$alertmsg && ($_POST['bn_save'] || $_POST['bn_save_close'] || $_POST['bn_save_stay'])) {
        $main_provid = 0 + $_POST['ProviderID'];
        $main_supid  = 0 + (int)$_POST['SupervisorID'];
        //.....

--------


Logical Should Use Symbolic Operators
=====================================

.. _cleverstyle-php-logicalinletters:

Cleverstyle
^^^^^^^^^^^

:ref:`logical-should-use-symbolic-operators`, in /modules/Uploader/Mime/Mime.php:171. 

$extension is assigned with the results of pathinfo($reference_name, PATHINFO_EXTENSION) and ignores static::hasExtension($extension). The same expression, placed in a condition (like an if), would assign a value to $extension and use another for the condition itself. Here, this code is only an expression in the flow.

.. code-block:: php

    $extension = pathinfo($reference_name, PATHINFO_EXTENSION) and static::hasExtension($extension);

--------



.. _openconf-php-logicalinletters:

OpenConf
^^^^^^^^

:ref:`logical-should-use-symbolic-operators`, in /chair/export.inc:143. 

In this context, the priority of execution is used on purpose; $coreFile only collect the temporary name of the export file, and when this name is empty, then the second operand of OR is executed, though never collected. Since this second argument is a 'die', its return value is lost, but the initial assignation is never used anyway. 

.. code-block:: php

    $coreFile = tempnam('/tmp/', 'ocexport') or die('could not generate Excel file (6)')

--------


Timestamp Difference
====================

.. _zurmo-structures-timestampdifference:

Zurmo
^^^^^

:ref:`timestamp-difference`, in app/protected/modules/import/jobs/ImportCleanupJob.php:73. 

This is wrong twice a year, in countries that has day-ligth saving time. One of the weeks will be too short, and the other will be too long. 

.. code-block:: php

    /**
             * Get all imports where the modifiedDateTime was more than 1 week ago.  Then
             * delete the imports.
             * (non-PHPdoc)
             * @see BaseJob::run()
             */
            public function run()
            {
                $oneWeekAgoTimeStamp = DateTimeUtil::convertTimestampToDbFormatDateTime(time() - 60 * 60 *24 * 7);

--------



.. _shopware-structures-timestampdifference:

shopware
^^^^^^^^

:ref:`timestamp-difference`, in engine/Shopware/Controllers/Backend/Newsletter.php:150. 

When daylight saving strike, the email may suddenly be locked for 1 hour minus 30 seconds ago. The lock will be set for the rest of the hour, until the server catch up. 

.. code-block:: php

    // Check lock time. Add a buffer of 30 seconds to the lock time (default request time)
                if (!empty($mailing['locked']) && strtotime($mailing['locked']) > time() - 30) {
                    echo "Current mail: '" . $subjectCurrentMailing . "'\n";
                    echo "Wait " . (strtotime($mailing['locked']) + 30 - time()) . " seconds ...\n";
                    return;
                }

--------


Identical Conditions
====================

.. _wordpress-structures-identicalconditions:

WordPress
^^^^^^^^^

:ref:`identical-conditions`, in wp-admin/theme-editor.php:247. 

The condition checks first if $has_templates or $theme->parent(), and one of the two is sufficient to be valid. Then, it checks again that $theme->parent() is activated with &&. This condition may be reduced to simply calling $theme->parent(), as $has_template is unused here.

.. code-block:: php

    <?php if ( ( $has_templates || $theme->parent() ) && $theme->parent() ) : ?>

--------



.. _dolibarr-structures-identicalconditions:

Dolibarr
^^^^^^^^

:ref:`identical-conditions`, in /htdocs/core/lib/files.lib.php:2052. 

Better check twice that $modulepart is really 'apercusupplier_invoice'.

.. code-block:: php

    $modulepart == 'apercusupplier_invoice' || $modulepart == 'apercusupplier_invoice'

--------



.. _mautic-structures-identicalconditions:

Mautic
^^^^^^

:ref:`identical-conditions`, in /app/bundles/CoreBundle/Views/Standard/list.html.php:47. 

When the line is long, it tends to be more and more difficult to review the values. Here, one of the two first is too many.

.. code-block:: php

    !empty($permissions[$permissionBase . ':deleteown']) || !empty($permissions[$permissionBase . ':deleteown']) || !empty($permissions[$permissionBase . ':delete'])

--------


Dont Echo Error
===============

.. _churchcrm-security-dontechoerror:

ChurchCRM
^^^^^^^^^

:ref:`dont-echo-error`, in wp-admin/includes/misc.php:74. 

This is classic debugging code that should never reach production. mysqli_error() and mysqli_errno() provide valuable information is case of an error, and may be exploited by intruders.

.. code-block:: php

    if (mysqli_error($cnInfoCentral) != '') {
            echo gettext('An error occured: ').mysqli_errno($cnInfoCentral).'--'.mysqli_error($cnInfoCentral);
        } else {

--------



.. _phpdocumentor-security-dontechoerror:

Phpdocumentor
^^^^^^^^^^^^^

:ref:`dont-echo-error`, in src/phpDocumentor/Plugin/Graphs/Writer/Graph.php:77. 

Default development behavior : display the caught exception. Production behavior should not display that message, but log it for later review. Also, the return in the catch should be moved to the main code sequence.

.. code-block:: php

    public function processClass(ProjectDescriptor $project, Transformation $transformation)
        {
            try {
                $this->checkIfGraphVizIsInstalled();
            } catch (\Exception $e) {
                echo $e->getMessage();
    
                return;
            }

--------


Could Be Private Class Constant
===============================

.. _phinx-classes-couldbeprivateconstante:

Phinx
^^^^^

:ref:`could-be-private-class-constant`, in /src/Phinx/Db/Adapter/MysqlAdapter.php:46. 

The code includes a fair number of class constants. The one listed here are only used to define TEXT columns in MySQL, with their maximal size. Since they are only intented to be used by the MySQL driver, they may be private.

.. code-block:: php

    class MysqlAdapter extends PdoAdapter implements AdapterInterface
    {
    
    //.....
        const TEXT_SMALL   = 255;
        const TEXT_REGULAR = 65535;
        const TEXT_MEDIUM  = 16777215;
        const TEXT_LONG    = 4294967295;

--------


Next Month Trap
===============

.. _contao-structures-nextmonthtrap:

Contao
^^^^^^

:ref:`next-month-trap`, in /system/modules/calendar/classes/Events.php:515. 

This code is wrong on August 29,th 30th and 31rst : 6 months before is caculated here as February 31rst, so march 2. Of course, this depends on the leap years.

.. code-block:: php

    case 'past_180':
    				return array(strtotime('-6 months'), time(), $GLOBALS['TL_LANG']['MSC']['cal_empty']);

--------



.. _edusoho-structures-nextmonthtrap:

Edusoho
^^^^^^^

:ref:`next-month-trap`, in /src/AppBundle/Controller/Admin/AnalysisController.php:1426. 

The last month is wrong 8 times a year : on 31rst, and by the end of March. 

.. code-block:: php

    'lastMonthStart' => date('Y-m-d', strtotime(date('Y-m', strtotime('-1 month')))),
                'lastMonthEnd' => date('Y-m-d', strtotime(date('Y-m', time())) - 24 * 3600),
                'lastThreeMonthsStart' => date('Y-m-d', strtotime(date('Y-m', strtotime('-2 month')))),

--------


Identical On Both Sides
=======================

.. _phpmyadmin-structures-identicalonbothsides:

phpMyAdmin
^^^^^^^^^^

:ref:`identical-on-both-sides`, in libraries/classes/DatabaseInterface.php:323. 

This code looks like ``($options & DatabaseInterface::QUERY_STORE) == DatabaseInterface::QUERY_STORE``, which would make sense. But PHP precedence is actually executing ``$options & (DatabaseInterface::QUERY_STORE == DatabaseInterface::QUERY_STORE)``, which then doesn't depends on QUERY_STORE but only on $options.

.. code-block:: php

    if ($options & DatabaseInterface::QUERY_STORE == DatabaseInterface::QUERY_STORE) {
        $tmp = $this->_extension->realQuery('
            SHOW COUNT(*) WARNINGS', $this->_links[$link], DatabaseInterface::QUERY_STORE
        );
        $warnings = $this->fetchRow($tmp);
    } else {
        $warnings = 0;
    }

--------


Join file()
===========

.. _wordpress-performances-joinfile:

WordPress
^^^^^^^^^

:ref:`join-file()`, in wp-admin/includes/misc.php:74. 

This code actually loads the file, join it, then split it again. file() would be sufficient. 

.. code-block:: php

    $markerdata = explode( "\n", implode( '', file( $filename ) ) );

--------



.. _spip-performances-joinfile:

SPIP
^^^^

:ref:`join-file()`, in ecrire/inc/install.php:109. 

When the file is not accessible, file() returns null, and can't be processed by join(). 

.. code-block:: php

    $s = @join('', file($file));

--------



.. _expressionengine-performances-joinfile:

ExpressionEngine
^^^^^^^^^^^^^^^^

:ref:`join-file()`, in ExpressionEngine_Core2.9.2/system/expressionengine/libraries/simplepie/idn/idna_convert.class.php:100. 

join('', ) is used as a replacement for file_get_contents(), which was introduced in PHP 4.3.0.

.. code-block:: php

    if (function_exists('file_get_contents')) {
        $this->NP = unserialize(file_get_contents(dirname(__FILE__).'/npdata.ser'));
    } else {
        $this->NP = unserialize(join('', file(dirname(__FILE__).'/npdata.ser')));
    }

--------



.. _prestashop-performances-joinfile:

PrestaShop
^^^^^^^^^^

:ref:`join-file()`, in classes/module/Module.php:2972. 

implode('', ) is probably not the slowest part in these lines.

.. code-block:: php

    $override_file = file($override_path);
    
    eval(preg_replace(array('#^\s*<\?(?:php)?#', '#class\s+'.$classname.'\s+extends\s+([a-z0-9_]+)(\s+implements\s+([a-z0-9_]+))?#i'), array(' ', 'class '.$classname.'OverrideOriginal_remove'.$uniq), implode('', $override_file)));
    $override_class = new ReflectionClass($classname.'OverrideOriginal_remove'.$uniq);
    
    $module_file = file($this->getLocalPath().'override/'.$path);
    eval(preg_replace(array('#^\s*<\?(?:php)?#', '#class\s+'.$classname.'(\s+extends\s+([a-z0-9_]+)(\s+implements\s+([a-z0-9_]+))?)?#i'), array(' ', 'class '.$classname.'Override_remove'.$uniq), implode('', $module_file)));

--------


Use pathinfo() Arguments
========================

.. _zend-config-php-usepathinfoargs:

Zend-Config
^^^^^^^^^^^

:ref:`use-pathinfo()-arguments`, in src/Factory.php:74:90. 

The `$filepath` is broken into pieces, and then, only the 'extension' part is used. With the PATHINFO_EXTENSION constant used as a second argument, only this value could be returned. 

.. code-block:: php

    $pathinfo = pathinfo($filepath);
    
            if (! isset($pathinfo['extension'])) {
                throw new Exception\RuntimeException(sprintf(
                    'Filename "%s" is missing an extension and cannot be auto-detected',
                    $filename
                ));
            }
    
            $extension = strtolower($pathinfo['extension']);
            // Only $extension is used beyond that point

--------



.. _thinkphp-php-usepathinfoargs:

ThinkPHP
^^^^^^^^

:ref:`use-pathinfo()-arguments`, in ThinkPHP/Extend/Library/ORG/Net/UploadFile.class.php:508. 

Without any other check, pathinfo() could be used with PATHINFO_EXTENSION.

.. code-block:: php

    private function getExt($filename) {
            $pathinfo = pathinfo($filename);
            return $pathinfo['extension'];
        }

--------


Compare Hash
============

.. _traq-security-comparehash:

Traq
^^^^

:ref:`compare-hash`, in /src/Models/User.php:105. 

This code should also avoid using SHA1. 

.. code-block:: php

    sha1($password) == $this->password

--------



.. _livezilla-security-comparehash:

LiveZilla
^^^^^^^^^

:ref:`compare-hash`, in livezilla/_lib/objects.global.users.inc.php:1391. 

This code is using the stronger SHA256 but compares it to another string. $_token may be non-empty, and still be comparable to 0. 

.. code-block:: php

    function IsValidToken($_token)
    {
        if(!empty($_token))
            if(hash("sha256",$this->Token) == $_token)
                return true;
        return false;
    }

--------


Register Globals
================

.. _teampass-security-registerglobals:

TeamPass
^^^^^^^^

:ref:`register-globals`, in api/index.php:25. 

The API starts with security features, such as the whitelist(). The whitelist applies to IP addresses, so the query string is not sanitized. Then, the QUERY_STRING is parsed, and creates a lot of new global variables.

.. code-block:: php

    teampass_whitelist();
    
    parse_str($_SERVER['QUERY_STRING']);
    $method = $_SERVER['REQUEST_METHOD'];
    $request = explode("/", substr(@$_SERVER['PATH_INFO'], 1));

--------



.. _xoops-security-registerglobals:

XOOPS
^^^^^

:ref:`register-globals`, in htdocs/modules/system/admin/images/main.php:33:33. 

This code only exports the POST variables as globals. And it does clean incoming variables, but not all of them. 

.. code-block:: php

    // Check users rights
    if (!is_object($xoopsUser) || !is_object($xoopsModule) || !$xoopsUser->isAdmin($xoopsModule->mid())) {
        exit(_NOPERM);
    }
    
    //  Check is active
    if (!xoops_getModuleOption('active_images', 'system')) {
        redirect_header('admin.php', 2, _AM_SYSTEM_NOTACTIVE);
    }
    
    if (isset($_POST)) {
        foreach ($_POST as $k => $v) {
            ${$k} = $v;
        }
    }
    
    // Get Action type
    $op = system_CleanVars($_REQUEST, 'op', 'list', 'string');

--------


Dont Echo Error
===============

.. _churchcrm-security-dontechoerror:

ChurchCRM
^^^^^^^^^

:ref:`dont-echo-error`, in wp-admin/includes/misc.php:74. 

This is classic debugging code that should never reach production. mysqli_error() and mysqli_errno() provide valuable information is case of an error, and may be exploited by intruders.

.. code-block:: php

    if (mysqli_error($cnInfoCentral) != '') {
            echo gettext('An error occured: ').mysqli_errno($cnInfoCentral).'--'.mysqli_error($cnInfoCentral);
        } else {

--------



.. _phpdocumentor-security-dontechoerror:

Phpdocumentor
^^^^^^^^^^^^^

:ref:`dont-echo-error`, in src/phpDocumentor/Plugin/Graphs/Writer/Graph.php:77. 

Default development behavior : display the caught exception. Production behavior should not display that message, but log it for later review. Also, the return in the catch should be moved to the main code sequence.

.. code-block:: php

    public function processClass(ProjectDescriptor $project, Transformation $transformation)
        {
            try {
                $this->checkIfGraphVizIsInstalled();
            } catch (\Exception $e) {
                echo $e->getMessage();
    
                return;
            }

--------


Logical Should Use Symbolic Operators
=====================================

.. _cleverstyle-php-logicalinletters:

Cleverstyle
^^^^^^^^^^^

:ref:`logical-should-use-symbolic-operators`, in /modules/Uploader/Mime/Mime.php:171. 

$extension is assigned with the results of pathinfo($reference_name, PATHINFO_EXTENSION) and ignores static::hasExtension($extension). The same expression, placed in a condition (like an if), would assign a value to $extension and use another for the condition itself. Here, this code is only an expression in the flow.

.. code-block:: php

    $extension = pathinfo($reference_name, PATHINFO_EXTENSION) and static::hasExtension($extension);

--------



.. _openconf-php-logicalinletters:

OpenConf
^^^^^^^^

:ref:`logical-should-use-symbolic-operators`, in /chair/export.inc:143. 

In this context, the priority of execution is used on purpose; $coreFile only collect the temporary name of the export file, and when this name is empty, then the second operand of OR is executed, though never collected. Since this second argument is a 'die', its return value is lost, but the initial assignation is never used anyway. 

.. code-block:: php

    $coreFile = tempnam('/tmp/', 'ocexport') or die('could not generate Excel file (6)')

--------


