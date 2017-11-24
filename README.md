# 一些小代码

## scrollwatch.js 
Js监视滚动条滚动进行菜单切换.

## mysql_export

导出数据

```
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
```