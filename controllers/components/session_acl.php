<?php

/**
 * AclComponent using Session Cache for CakePHP 1.3
 *
 * Copyright 2011, nojimage (http://php-tips.com/)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @author     nojimage
 * @package    elastic_kit
 * @subpackage elastic_kit.controllers.components
 * @copyright  2011 nojimage (http://php-tips.com/)
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 * =====
 * Original Code is:
 *      macduy
 *          http://bakery.cakephp.org/articles/macduy/2010/01/05/acl-caching-using-session
 *
 *      Thank you!
 */
App::import('Component', 'Acl');

class SessionAclComponent extends AclComponent {

    public $components = array('Session');
    /**
     *
     * @var array
     */
    public $settings = array(
        'sessionKey' => 'Acl',
        'flushAtInit' => false,
    );
    /**
     *
     * @var SessionComponent
     */
    public $Session;

    /**
     *
     * @param AppController $controller
     * @param array $settings
     */
    function initialize($controller, $settings = array()) {
        $controller->Acl = $this;
        $this->settings = am($this->settings, $settings);

        if ($this->settings['flushAtInit']) {
            $this->flushCache();
        }
    }

    /**
     * Pass-thru function for ACL check instance.  Check methods
     * are used to check whether or not an ARO can access an ACO
     *
     * @param string $aro ARO The requesting object identifier.
     * @param string $aco ACO The controlled object identifier.
     * @param string $action Action (defaults to *)
     * @return boolean Success
     * @access public
     */
    function check($aro, $aco, $action = "*") {
        $path = $this->__cachePath($aro, $aco, $action);
        if ($this->Session->check($path)) {
            return $this->Session->read($path);
        } else {
            $check = parent::check($aro, $aco, $action);
            $this->Session->write($path, $check);
            return $check;
        }
    }

    /**
     * Pass-thru function for ACL allow instance. Allow methods
     * are used to grant an ARO access to an ACO.
     *
     * @param string $aro ARO The requesting object identifier.
     * @param string $aco ACO The controlled object identifier.
     * @param string $action Action (defaults to *)
     * @return boolean Success
     * @access public
     */
    function allow($aro, $aco, $action = "*") {
        $this->flushCache();
        return parent::allow($aro, $aco, $action);
    }

    /**
     * Pass-thru function for ACL deny instance. Deny methods
     * are used to remove permission from an ARO to access an ACO.
     *
     * @param string $aro ARO The requesting object identifier.
     * @param string $aco ACO The controlled object identifier.
     * @param string $action Action (defaults to *)
     * @return boolean Success
     * @access public
     */
    function deny($aro, $aco, $action = "*") {
        $this->flushCache();
        return parent::deny($aro, $aco, $action);
    }

    /**
     * Pass-thru function for ACL inherit instance. Inherit methods
     * modify the permission for an ARO to be that of its parent object.
     *
     * @param string $aro ARO The requesting object identifier.
     * @param string $aco ACO The controlled object identifier.
     * @param string $action Action (defaults to *)
     * @return boolean Success
     * @access public
     */
    function inherit($aro, $aco, $action = "*") {
        $this->flushCache();
        return parent::inherit($aro, $aco, $action);
    }

    /**
     * Pass-thru function for ACL grant instance. An alias for AclComponent::allow()
     *
     * @param string $aro ARO The requesting object identifier.
     * @param string $aco ACO The controlled object identifier.
     * @param string $action Action (defaults to *)
     * @return boolean Success
     * @access public
     */
    function grant($aro, $aco, $action = "*") {
        $this->flushCache();
        return parent::grant($aro, $aco, $action);
    }

    /**
     * Pass-thru function for ACL grant instance. An alias for AclComponent::deny()
     *
     * @param string $aro ARO The requesting object identifier.
     * @param string $aco ACO The controlled object identifier.
     * @param string $action Action (defaults to *)
     * @return boolean Success
     * @access public
     */
    function revoke($aro, $aco, $action = "*") {
        $this->flushCache();
        return parent::revoke($aro, $aco, $action);
    }

    /**
     * Returns a unique, dot separated path to use as the cache key. Copied from CachedAcl.
     *
     * @param string $aro ARO
     * @param string $aco ACO
     * @param boolean $acoPath Boolean to return only the path to the ACO or the full path to the permission.
     * @access private
     */
    function __cachePath($aro, $aco, $action, $acoPath = false) {
        if ($action != "*") {
            $aco .= '/' . $action;
        }
        $path = Inflector::slug($aco);

        if (!$acoPath) {
            $_aro = array();
            if (!is_array($aro)) {
                $_aro = explode(':', $aro);
            } elseif (Set::countDim($aro) > 1) {
                $_aro = array(key($aro), current(current($aro)));
            } else {
                $_aro = array_values($aro);
            }
            $path .= '.' . Inflector::slug(implode('.', $_aro));
        }

        return $this->settings['sessionKey'] . '.' . $path;
    }

    /**
     * Deletes the whole cache from the Session variable
     */
    function flushCache() {
        $this->Session->delete($this->settings['sessionKey']);
    }

    /**
     * Checks that ALL of given pairs of aco-action are satisfied
     */
    function all($aro, $pairs) {
        foreach ($pairs as $aco => $action) {
            if (!$this->check($aro, $aco, $action)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Checks that AT LEAST ONE of given pairs of aco-action is satisfied
     */
    function one($aro, $pairs) {
        foreach ($pairs as $aco => $action) {
            if ($this->check($aro, $aco, $action)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns an array of booleans for each $aco-$aro pair
     */
    function can($aro, $pairs) {
        $can = array();
        $i = 0;
        foreach ($pairs as $aco => $action) {
            $can[$i] = $this->check($aro, $aco, $action);
            $i++;
        }
        return $can;
    }

}