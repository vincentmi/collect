<?php

/**
 * Created by vincentmi @ 17/11/23 20:59
 * @aurhor vincentmi
 */
class Exporter
{

    const EXPORT_CSV = 'csv';
    const EXPORT_TABLE = 'table';
    const EXPORT_XML = 'xml';


    public $dsn = 'mysql:host={host};port={port};dbname={db};charset={charset}';
    private $user = 'root';
    private $pass = 'root';
    private $dnsAttrs = [];
    public $chunk = 1000;
    public $export = 'csv';

    private $connect = null;

    public function __construct($host, $user, $pass, $options)
    {

        $this->dnsAttrs = [
            '{host}' => $host,
            '{user}' => $user,
            '{pass}' => $pass,
            '{port}' => isset($options['port']) ? $options['port'] : 3306,
            '{db}' => isset($options['db']) ? $options['db'] : 'test',
            '{charset}' => isset($options['charset']) ? $options['charset'] : 'UTF8',

        ];
        $this->dsn = strtr($this->dsn, $this->dnsAttrs);
    }

    private function getConnection()
    {
        if ($this->connect == null) {
            $this->connect = new PDO($this->dsn, $this->user, $this->pass);
            $this->connect->exec("SET NAMES UTF8");
        }
        return $this->connect;
    }

    public function throwError($data = '')
    {
        $error = print_r($data);
        $error .= print_r($this->getConnection()->errorInfo(), true);
        throw new \Exception('Query error . ' . $error);
    }


    public function queryToFile($sqlinfo, $file)
    {
        $run = true;
        $i = 0;
        if (is_array($sqlinfo)) {
            $sql = $sqlinfo[0];
            $bind = $sqlinfo[1];
        } else {
            $sql = $sqlinfo;
            $bind = [];
        }

        $fp = fopen($file, 'w');
        fwrite($fp, $this->header());

        while ($run) {
            $sqlChunk = $sql . ' LIMIT ' . ($i * $this->chunk) . ',' . $this->chunk;
            $data = null;
            if ($bind) {
                $q = $this->getConnection()->prepare($sqlChunk);
                $q->setFetchMode(PDO::FETCH_ASSOC);
                if ($q->execute($bind) === false) {
                    $this->throwError();
                } else {
                    $data = $q->fetchAll();
                }
                $q->closeCursor();
            } else {
                $q = $this->getConnection()->query($sqlChunk, PDO::FETCH_ASSOC);
                if ($q === false) {
                    $this->throwError();
                } else {
                    $data = $q->fetchAll();
                }
                $q->closeCursor();
            }
            if (!$data) {
                $run = false;
            } else {

                echo 'export-' . count($data) . "\n";
                $content = '';
                if ($i == 0) {
                    $content .= $this->title(array_keys($data[0]));
                }
                foreach ($data as $row) {
                    $content .= $this->row($row);
                }
                fwrite($fp, $content);
            }
            $i++;

            //write data
        }
        fwrite($fp, $this->footer());
        fclose($fp);
    }


    private function header()
    {
        if ($this->export == self::EXPORT_CSV) {
            return '';

        } elseif ($this->export == self::EXPORT_TABLE) {
            return '<table border="1">';
        }
    }

    private function footer()
    {
        if ($this->export == self::EXPORT_CSV) {
            return "";

        } elseif ($this->export == self::EXPORT_TABLE) {
            return '</table>';
        }
    }

    private function title($titles)
    {
        if ($this->export == static::EXPORT_TABLE) {
            $content = '<tr>';
            foreach ($titles as $k) {
                $content .= '<th>' . $k . '</th>';
            }
            $content .= '</tr>';
            return $content;
        } else if ($this->export == static::EXPORT_CSV) {
            $content = '';
            foreach ($titles as $k) {
                $content .= $this->csvQuote($k) . ',';
            }
            $content = substr($content,0,-1). "\n";
            return $content;
        }

    }

    private function csvQuote($str){
        $str  = str_replace('"','\\"',$str);
        $str  = str_replace("\r","\\r",$str);
        $str  = str_replace("\n","\\n",$str);
        return '"'.$str.'"';
    }

    private function row($row)
    {
        if ($this->export == static::EXPORT_TABLE) {
            $content = '<tr>';
            foreach ($row as $k => $v) {
                $content .= '<td>' . htmlspecialchars($v) . '</td>';
            }
            $content .= '</tr>';
            return $content;
        } else if ($this->export == static::EXPORT_CSV) {
            $content = '';
            foreach ($row as $k => $v) {
                $content .= $this->csvQuote($v) . ',';
            }
            $content = substr($content,0,-1). "\n";
            return $content;
        }

    }


    public function execute($sql, $file = '')
    {
        $this->queryToFile($sql, $file);
    }


}