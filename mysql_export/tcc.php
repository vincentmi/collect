<?php


include 'Exporter.php';

$clientId = 109 ;

Exporter::create()
    ->host('127.0.0.1')
    ->db('tcc2')
    ->user('tcc2user')
    ->password('tcc2@godlove')
    ->chunk(3000)
    ->type(Exporter::EXPORT_CSV)
    ->query('
      SELECT 
	a.title , a.body,
	FROM_UNIXTIME(a.created) AS created_at,
	c.`catName`
      FROM article_content  a 
      LEFT JOIN article_categories c ON a.cid = c.id
      WHERE  a.clientId =  '.$clientId,
        'article.csv')
    ->query('SELECT * FROM gbook WHERE clientId = '.$clientId,'gbook.csv')
    ->query('select * from model','model.csv')
    ->perform()

;