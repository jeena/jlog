<?php
 include_once('.'.DIRECTORY_SEPARATOR.'auth.php');
 define("JLOG_ADMIN", true);
 require_once('..'.DIRECTORY_SEPARATOR.'personal'.DIRECTORY_SEPARATOR.'settings.inc.php');

 $c['meta']['title'] .= "BBCode Anleitung";

 $c['main'] .= output_admin_menu().'

 <h2>BBCode Anleitung</h2>
 <p><a href="#links">Links</a> | <a href="#betonung">Betonung</a> |
    <a href="#bilder">Bilder</a> | <a href="#ueberschrift">�berschrift</a> |
    <a href="#listen">Listen</a> | <a href="#zitate">Zitate</a> | <a href="#html">HTML</a>
 </p>
 <h3>Allgemein zu Zeilenumbr�chen, Abs�tzen und Leerzeichen</h3>
 <p><kbd>[Enter]</kbd> im normalen Flie�text darf nur in Paaren vorkommen, wenn man
    einen richtigen <em>Absatz</em> erzeugen will. Vor jedem 
    <abbr title="Elemente, die automatisch einen neue Zeile anfangen">block</abbr> Element
    muss zwei mal <kbd>[Enter]</kbd> gedr�ckt werden, da dies so zu sagen einen neuen
    Absatz einleitet.
 </p>
 <p>Man sollte <em>nie</em> nur einen
    Enter einzeln setzen, um die Breite des Textes zu bestimmen. Das macht
    die Auszeichnungssprache <abbr title="Xtensible Hypertext Markup Language">XHTML</abbr>
    n�mlich von ganz alleine, je nachdem wie viel Platz der Besucher auf seinem Bildschirm
    hat. Auch brauchst du keine Angst haben dass der Platz im Eingabefensterchen
    nicht ausreicht, wenn du einen langen URL schreibst, das bricht auch automatisch um.
    Bitte also keinen [Enter] vor einen URL setzen nur damit das in der neuen Zeile Anf�ngt.
 </p>
 <p><strong>Leerzeichen</strong> m�ssen vor und hinter jedes <abbr title="Elemente die keinen neuen Absatz erzeugen">inline</abbr> Element.
    Innerhalb von <abbr title="Elemente die keinen neuen Absatz erzeugen">inline</abbr> Elementen d�rfen Leerzeichen nur zwischen zwei W�rtern
    stehen, niemals aber direkt hinter dem einleitenden Tag, also <em>niemals</em> so:<br />
    <code>[url=http://exapmle.tld]<a href="#">[Leerzeichen]Beispiel Link</a>[Leerzeichen][/url]</code><br />
    sondern in etwa so: <code>[url=http://example.tld]<a href="#">Beispiel Link</a>[/url]</code>.
 </p>
 <p>Die einzige Ausnahme dieser Regel ist, wenn man nur einen Teil eines Wortes als
    etwas besonderes auszeichnen will. Dann macht man es zum Beispiel so:<br />
    <code>Inter[url=http://communication.tld]kommunikation[/url]smanagment</code><br />
    welches ergibt: <code>Inter<a href="#">kommunikation</a>smanagment</code>
 </p>
 <p>Vor einem Punkt �.� kommt niemals ein Leerzeichen, sondern immer dahinter.
 </p>
 
 <h3 id="links">Links (<abbr title="Elemente die keinen neuen Absatz erzeugen">inline</abbr>)</h3>
 <ul>
  <li>[url]<a href="http://example.com">http://example.com</a>[/url]</li>
  <li>[url=http://example.com]<a href="http://example.com">Linkbeschreibung</a>[/url]</li>
 </ul>
 
 <h3 id="betonung">Betonung (<abbr title="Elemente die keinen neuen Absatz erzeugen">inline</abbr>)</h3>
 <ul>
  <li>[b]<strong>stark betont</strong>[/b]</li>
  <li>[i]<em>leicht betont</em>[/i]</li>
 </ul>
 
 <h3 id="bilder">Bilder ( <abbr title="Elemente die keinen neuen Absatz erzeugen">inline</abbr> /
    <abbr title="Elemente, die automatisch einen neue Zeile anfangen">block</abbr> )</h3>
 <p>Grunds�tzlich wird ein Bild so eingef�gt: <code>[img]1.jpg[/url]</code> und ist ein
    <abbr title="Elemente die keinen neuen Absatz erzeugen">inline</abbr> Element. Bei dir macht
    das ja sowieso das Script. Wenn du irgendwann ein Bild von einem anderen Server einf�gen
    m�chtest dann musst du es so machen: <code>[url]http://example.tld/pfad/zum/Bild.jpg[/img]</code>
    meistens ist das aber von anderen Webmastern nur sehr ungern gesehen. Bilder k�nnen noch drei
    Attribute enthalten:</p>
 <p><strong>Erstens:</strong>
    <code>[img class="klassenname"]1.jpg[/img]</code> welches dem Bild eine Klasse mitgibt.
    Man k�nnte z.B. eine Klasse <code>fr</code> in CSS so gestalten, dass Sie
    das Bild rechts floaten l�sst, oder eine Klasse <code>photo</code>, die allen
    Photos einen Wei�en Rahmen gibt usw. usf. Verschiedene Klassen sind durch Leerzeichen
    zu trennen.</p>
 <p><strong>Zweitens:</strong> <code>[img alt="Alternativ Text f�r das Bild"]1.jpg[/img]</code> welcher
    eigentlich immer angegeben werden sollte. Er nutzt Suchmaschinen, die keine Bilder
    sehen k�nnen und auch Menschen die keine Bilder sehen k�nnen, sei es dass sie
    Blind sind, oder Bilder abschalten um schneller surfen zu k�nnen, oder dass Ihr
    Browser keine Bilder anzeigen kann.</p>
 <p><strong>Drittens:</strong> <code>[img caption="Eine Bildunterschrift"</code>]1.jpg[/img]
    das Attribut <code>caption</code> ist anders als die anderen zwei. Es macht aus dem Bild
    eine Definitionsliste und somit ein <abbr title="Elemente, die automatisch einen neue
    Zeile anfangen">block</abbr> Element, der dann das <code>class</code> Attribut anstatt
    dem Bild �bergeben wird. Dar�ber Hinaus bekommt die Definitionsliste auch noch die Breite
    des Bildes. Der ausgegebene HTML Code sieht dann in Etwa so aus:</p>
 <p><code>
  &nbsp;&lt;dl class="photo fr" style="width: 200px;"&gt;<br />
  &nbsp;&nbsp;&lt;dt&gt;&lt;img alt="Ein Pferd" style="width: 200px; height: 150px;" /&gt;<br />
  &nbsp;&nbsp;&nbsp;&lt;dd&gt;Eine Bildunterschrift&lt;/dd&gt;<br />
  &nbsp;&lt;/dl&gt;</code></p>
 <p>Dies funktioniert nur bei Bildern, die �ber das Jlog hochgeladen wurden.</p>
 <h4>Vollst�ndig k�nnte das zum Beispiel so aussehen:</h4>
 <p><code>[img class="fr" alt="Ein sehr gro�er Pferdekopf" caption="Mein Pferd ist das
    sch�nste"]http://jeenaparadies.net/img/weblog/pferd.jpg[/img]</code></p>
 <p>Bilder sollten aber mit R�cksicht auf die Benutzer mit kleinem Bildschirm/Fenster
    und die Modembenutzer nicht all zu gro� sein. Alles was breiter als 500px
    ist, f�ngt an gef�hrlich zu werden.
 </p>

 <h3 id="ueberschrift">�berschrift (<abbr title="Elemente, die automatisch einen neue Zeile anfangen">block</abbr>)</h3>
  <div class="xmp">[headline]<h3>�berschrift erster Ordnung</h3>[/headline]</div>

 <h3 id="listen">Listen (<abbr title="Elemente, die automatisch einen neue Zeile anfangen">block</abbr>)</h3>
 <p>Hier auch wichtig davor und dahinter eine Leerzeile zu lassen, damit das auch als
    richtiger Absatz gesehen wird. Aber bitte hier auch keine unn�tigen Leerzeichen
    reinmachen.
 </p>

 <h4>Ungeordnete Liste (<abbr title="Elemente, die automatisch einen neue Zeile anfangen">block</abbr>)</h4>
 <p>[list]<br />
    [*]erster Punkt<br />
    [*]zweiter Punkt<br />
    [*]dritter Punkt<br />
    [/list]
 </p>
 <p>Ergibt:</p>
 <ul class="xmp">
  <li>erster Punkt</li>
  <li>zweiter Punkt</li>
  <li>dritter Punkt</li>
 </ul>
 
 <h4>Nummerierte Liste (<abbr title="Elemente, die automatisch einen neue Zeile anfangen">block</abbr>)</h4>
 <p>Genau so wie bei der ungeordneten, mit der Ausnahme, dass anstatt [list], [#list]
    verwendet wird. Das Ergebniss sieht dann so aus:
 </p>
 <ol class="xmp">
  <li>erster Punkt</li>
  <li>zweiter Punkt</li>
  <li>dritter Punkt</li>
 </ol>
 
 <h3 id="zitate">Zitate (<abbr title="Elemente, die automatisch einen neue Zeile anfangen">block</abbr>)</h3>
 <p>Bitte nach [quote] kein Leerzeichen und auch keinen Zeilenumbruch ein f�gen. Genau so
    bei [/quote] keinen davor.
 </p>
 <blockquote class="xmp">
  <p>[quote]Ich bin ein Zitat welches nur dazu da ist um zu zeigen wie
     das alles hier so geht. Und aber auch dazu um zu zeigen dass es mehrere Zeilen
     sein k�nnen.</p>
  <p>Oder auch mehrere Abs�tze wie jetzt zum Beispiel hier.[/quote]</p>
 </blockquote>
 <h3 id="html">HTML</h3>
 <p>Um normalen HTML Quelltext eingeben zu k�nnen, der von BBCode nicht behandelt wird,
    kann man [html] verwenden. Dann muss man sich allerdings um alles selbst k�mmern,
    auch um Abs�tze und Zeilenumbr�che.</p>
 ';

require(JLOG_BASEPATH.'scripts'.DIRECTORY_SEPARATOR.'do_template.php');
echo $body;

?>