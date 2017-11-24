<?php


include 'Exporter.php';

$e = new Exporter('192.168.33.1','root','root',['db'=>'ocm']);

Exporter::create()
    ->host('192.168.33.1')
    ->db('ocm')
    ->chunk(1000)
    ->type(Exporter::EXPORT_CSV)
    ->query('select * from catalog ','catalog.csv')
    ->query('select * from model','model.csv')
    ->perform()

;