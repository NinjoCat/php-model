<?php

namespace OLOG\DB;

class DBSettings
{
    //$this->pdo = new \PDO('mysql:host=' . $db_conf_arr['host'] . ';dbname=' . $db_conf_arr['db_name'] . ';charset=utf8', $db_conf_arr['user'], $db_conf_arr['pass']);
    protected $server_host;
    protected $db_name;
    protected $user;
    protected $password;
    protected $sql_file_path_in_project_root;

    public function __construct($server_host, $db_name, $user, $password, $sql_file_path_in_project_root = '')
    {
        $this->setServerHost($server_host);
        $this->setDbName($db_name);
        $this->setUser($user);
        $this->setPassword($password);
        $this->setSqlFilePathInProjectRoot($sql_file_path_in_project_root);
    }

    /**
     * @return mixed
     */
    public function getServerHost()
    {
        return $this->server_host;
    }

    /**
     * @param mixed $server_host
     */
    public function setServerHost($server_host)
    {
        $this->server_host = $server_host;
    }

    /**
     * @return mixed
     */
    public function getDbName()
    {
        return $this->db_name;
    }

    /**
     * @param mixed $db_name
     */
    public function setDbName($db_name)
    {
        $this->db_name = $db_name;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getSqlFilePathInProjectRoot()
    {
        return $this->sql_file_path_in_project_root;
    }

    /**
     * @param mixed $sql_file_path_in_project_root
     */
    public function setSqlFilePathInProjectRoot($sql_file_path_in_project_root)
    {
        $this->sql_file_path_in_project_root = $sql_file_path_in_project_root;
    }


}