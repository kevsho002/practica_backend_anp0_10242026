<?php

class Configuracion {
    public string $host;
    public string $db;
    public string $user;
    public string $pass;
    public string $charset;
    public string $dsn;

    public function __construct() {
        $this->host    = 'localhost';
        $this->db      = 'universidad';
        $this->user    = 'root';
        $this->pass    = 'Admin';
        $this->charset = 'utf8mb4';
        $this->dsn     = "mysql:host={$this->host};dbname={$this->db};charset={$this->charset}";
    }
}