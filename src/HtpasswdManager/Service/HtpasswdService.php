<?php

/**
 * @author Steven Bühner
 * @license MIT
 */
namespace HtpasswdManager\Service;

class HtpasswdService {
    private $filename;

    // Caching of htpasswd-file
    private $userListCache = NULL;
    private $htpasswdCache = NULL;

    // Static Variables
    protected static $REGULAR_USER_PASSWORD = '~^([^:]+):(.+)$~im';

    public function __construct($htpasswd_filename) {
        $this->filename = $htpasswd_filename;
        $this->createFileIfNotExistant();
    }

    private function createFileIfNotExistant() {
        if (false === file_exists($this->filename)) {
            touch($this->filename);
        }
    }

    private function getHtpasswdContent() {
        if ($this->htpasswdCache === NULL) {

            $this->htpasswdCache = file_get_contents($this->filename);
        }

        return $this->htpasswdCache;
    }

    private function updateHtpasswdContent() {
        $this->htpasswdCache = NULL;
        $this->userListCache = NULL;
        $this->getUserList();
    }

    public function getUserList() {
        if ($this->userListCache === NULL) {
            $result = array();

            $content = $this->getHtpasswdContent();

            if (preg_match_all($this::$REGULAR_USER_PASSWORD, $content, $matches, PREG_PATTERN_ORDER) !== false) {
                foreach ($matches [1] as $i => $user) {
                    $result [$user] = $matches [2] [$i];
                }
            }

            $this->userListCache = $result;
        }

        return $this->userListCache;
    }

    private function encodePassword($password) {
        return crypt($password, substr(str_replace('+', '.', base64_encode(pack('N4', mt_rand(), mt_rand(), mt_rand(), mt_rand()))), 0, 22));
    }

    private function getNewUserEncodedString($username, $password) {
        return $username . ':' . $this->encodePassword($password) . "\n";
    }

    public function addUser($username, $password) {
        $newContent = $this->getHtpasswdContent();
        $newContent .= $this->getNewUserEncodedString($username, $password);

        $this->replaceHtPasswdContent($newContent);
    }

    public function updateUser($username, $password) {
        if ($this->userExists($username)) {
            $this->deleteUser($username);
        }

        $this->addUser($username, $password);
    }

    public function deleteUser($username) {
        $newContent      = '';
        $usernameDeleted = false;

        if (preg_match_all($this::$REGULAR_USER_PASSWORD, $this->getHtpasswdContent(), $match) > 0) {
            foreach ($match [1] as $i => $user) {
                if ($user == $username) {
                    $usernameDeleted = true;
                } else {
                    $newContent .= $match [0] [$i] . "\n";
                }
            }

            if (true === $usernameDeleted) {
                $this->replaceHtPasswdContent($newContent);
            }
        }

        return $usernameDeleted;
    }

    private function replaceHtPasswdContent($newContent) {
        $fp = fopen($this->filename, 'w');
        fwrite($fp, $newContent);
        fclose($fp);

        $this->updateHtpasswdContent();
    }

    public function userExists($username) {
        $userList = $this->getUserList();

        if (isset ($userList [$username]))
            return true;

        return false;
    }

}
