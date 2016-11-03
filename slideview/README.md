# slideview.js

>
> Author : Vincent Mi
> 
> 

图片轮播的jquery插件.

使用

```html
<script src="../src/jquery.scrollwatch.js"></script>
``` 

HTML 

```html
<div class="container">
  <div class="nav">
    <ul>
      <li data-target="#content1"" >Content 1 </li>
      <li data-target=".content2"" >Content 2 </li>
      ...
    </ul>
    
    <div id="content1">
    ....
    </div>
    
    <div class="content2">
    ....
    </div>
    
  </div>
</div>
```

JS

```js
$(function(){
    $('.nav').scrollwatch({'container':'.container'})
})
```