<?php

if (!defined('CAKEPHP_UNIT_TEST_EXECUTION')) {
    define('CAKEPHP_UNIT_TEST_EXECUTION', 1);
}
App::import(array('controller' . DS . 'components' . DS . 'acl', 'model' . DS . 'db_acl'));
App::import('Component', array('SessionAcl.SessionAcl', 'Session'));

/**
 * AclNodeTwoTestBase class
 *
 * @package       cake
 * @subpackage    cake.tests.cases.libs.controller.components
 */
class AclNodeTwoTestBase extends AclNode {

    /**
     * useDbConfig property
     *
     * @var string 'test_suite'
     * @access public
     */
    var $useDbConfig = 'test_suite';
    /**
     * cacheSources property
     *
     * @var bool false
     * @access public
     */
    var $cacheSources = false;
}

/**
 * AroTwoTest class
 *
 * @package       cake
 * @subpackage    cake.tests.cases.libs.controller.components
 */
class AroTwoTest extends AclNodeTwoTestBase {

    /**
     * name property
     *
     * @var string 'AroTwoTest'
     * @access public
     */
    var $name = 'AroTwoTest';
    /**
     * useTable property
     *
     * @var string 'aro_twos'
     * @access public
     */
    var $useTable = 'aro_twos';
    /**
     * hasAndBelongsToMany property
     *
     * @var array
     * @access public
     */
    var $hasAndBelongsToMany = array('AcoTwoTest' => array('with' => 'PermissionTwoTest'));
}

/**
 * AcoTwoTest class
 *
 * @package       cake
 * @subpackage    cake.tests.cases.libs.controller.components
 */
class AcoTwoTest extends AclNodeTwoTestBase {

    /**
     * name property
     *
     * @var string 'AcoTwoTest'
     * @access public
     */
    var $name = 'AcoTwoTest';
    /**
     * useTable property
     *
     * @var string 'aco_twos'
     * @access public
     */
    var $useTable = 'aco_twos';
    /**
     * hasAndBelongsToMany property
     *
     * @var array
     * @access public
     */
    var $hasAndBelongsToMany = array('AroTwoTest' => array('with' => 'PermissionTwoTest'));
}

/**
 * PermissionTwoTest class
 *
 * @package       cake
 * @subpackage    cake.tests.cases.libs.controller.components
 */
class PermissionTwoTest extends CakeTestModel {

    /**
     * name property
     *
     * @var string 'PermissionTwoTest'
     * @access public
     */
    var $name = 'PermissionTwoTest';
    /**
     * useTable property
     *
     * @var string 'aros_aco_twos'
     * @access public
     */
    var $useTable = 'aros_aco_twos';
    /**
     * cacheQueries property
     *
     * @var bool false
     * @access public
     */
    var $cacheQueries = false;
    /**
     * belongsTo property
     *
     * @var array
     * @access public
     */
    var $belongsTo = array('AroTwoTest' => array('foreignKey' => 'aro_id'), 'AcoTwoTest' => array('foreignKey' => 'aco_id'));
    /**
     * actsAs property
     *
     * @var mixed null
     * @access public
     */
    var $actsAs = null;
}

class DbAclTwoTest extends DbAcl {

    function __construct() {
        $this->Aro = & new AroTwoTest();
        $this->Aro->Permission = & new PermissionTwoTest();
        $this->Aco = & new AcoTwoTest();
        $this->Aro->Permission = & new PermissionTwoTest();
    }

}

class IniAclTest extends IniAcl {

}

class AclComponentTest extends CakeTestCase {

    var $fixtures = array('core.aro_two', 'core.aco_two', 'core.aros_aco_two');

    function startTest() {
        $this->SessionAcl = new SessionAclComponent();
        $this->SessionAcl->Session = new SessionComponent();
        $this->Acl = new AclComponent();
        $this->SessionAcl->flushCache();

        $this->count = 100;
        $this->aroName = 'Micheal';
        $this->acoName = 'print';
        $this->action = '*';

        $this->time = microtime(true);
    }

    function endTest($methodName) {
        echo "<h3>{$methodName}</h3>";
        $time = microtime(true) - $this->time;
        debug(sprintf('%0.3f sec (av %0.4f)', $time, $time / $this->count));
    }

    function before($method) {
        Configure::write('Acl.classname', 'DbAclTwoTest');
        Configure::write('Acl.database', 'test_suite');
        parent::before($method);
    }

    function tearDown() {
        unset($this->Acl);
        unset($this->SessionAcl);
    }

    function testAclCheck() {
        for ($i = 0; $i < $this->count; $i++) {
            $this->Acl->check($this->aroName, $this->acoName, $this->action);
        }
    }

    function testSessionAclCheck() {
        for ($i = 0; $i < $this->count; $i++) {
            $this->SessionAcl->check($this->aroName, $this->acoName, $this->action);
        }
    }

}
