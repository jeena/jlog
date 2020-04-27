<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<head>
 <title><jlog:title /> - <jlog:website /></title>
   <jlog:aditional-header />
   <link rel="stylesheet" href="<jlog:homelink />/personal/css/print.css"  type="text/css" media="print" />
   <link rel="stylesheet" href="<jlog:homelink />/personal/css/screen.css" type="text/css" media="screen, projection" />
   <!--[if lte IE 6]><link rel="stylesheet" href="<jlog:homelink />/personal/css/ielte6.css" type="text/css" media="screen" /><![endif]-->
</head>
<body>
 <p class="skip"><a href="#main"><jlog:skipcontent /></a></p>
 <div id="container"><span id="pokal"></span>
  <h1><a title="<jlog:home />" href="<jlog:homelink />/"><jlog:website /></a></h1>
  <dl id="subnav">
   <dt class="first"><jlog:slogan-h /></dt>
    <dd><jlog:slogan /></dd>
   <dt><jlog:search-h /></dt>
    <dd>
     <form action="<jlog:homelink />/search.php">
      <p><input class="userdata" type="text" name="q" size="15" value="" />
         <input class="send" type="submit" value="<jlog:search />" /></p>
     </form>
    </dd>
   <dt><jlog:categorieslist-h /></dt>
    <dd><jlog:categorieslist /></dd>
   <dt><jlog:current-h /></dt>
    <dd><jlog:subcurrent /></dd>
    <dd><jlog:archive-more /> <a href="<jlog:archivelink />"><jlog:archive /></a>.</dd>
   <dt><jlog:sub-info /></dt>
    <dd class="rss"><jlog:rss-link /><br /> <jlog:rss-info /></dd>
    <dd><jlog:copyright /></dd>
    <dd><jlog:powered /></dd>
  </dl>
  <div id="main">
   <jlog:content />
<html lang="<jlog:html-language />">
  </div>
  <div id="footer">&nbsp;</div>
 </div>
</body>
</html>
