<!DOCTYPE html>
<html lang="<jlog:html-language />">
 <head>
  <title><jlog:title /> - <jlog:website /></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <jlog:aditional-header />
  <link rel="stylesheet" href="<jlog:homelink />/personal/css/print.css"  type="text/css" media="print" />
  <link rel="stylesheet" href="<jlog:homelink />/personal/css/screen.css" type="text/css" media="screen, projection" />
 </head>
 <body>
  <p class="skip"><a href="#main"><jlog:skipcontent /></a></p>
  <div id="container">
   <span id="pokal"></span>
   <header>
    <h1><a title="<jlog:home />" href="<jlog:homelink />/"><jlog:website /></a></h1>
   </header>
   <aside>
    <dl id="subnav">
     <dt class="first"><jlog:slogan-h /></dt>
      <dd><jlog:slogan /></dd>
     <dt><jlog:search-h /></dt>
      <dd>
       <form action="<jlog:homelink />/search.php">
        <p><input class="userdata" type="search" name="q" size="15" value="" />
         <button class="send" type="submit" value="<jlog:search />"><jlog:search /></button></p>
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
   </aside>
   <main id="main">
    <jlog:content />
   </main>
   <footer>&nbsp;</footer>
  </div>
 </body>
</html>
