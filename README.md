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

## git-pack

收集提交中进行变化的文件,保持目录结构拷贝出来

```sh
git-pack  ac89c79fd0198b32d6a993a82aa5195da6ee0510 8557b684390ba00c07f8758ddaa95f0307cfad43 904f5a9465708a4cc60b0fde4b246400397f67d8

```