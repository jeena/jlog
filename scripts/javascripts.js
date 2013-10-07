// TODO: I'd like to have an extra "js" directory for all JavaScripts
// a theme needs.

/*
 * Jlogs not minified version of JavaScript
 */

function jlog_bbcode(insText, aTag, eTag) {
    if (!insText) { return ''; }
    return aTag + insText + eTag;
}

function jlog_bbcode_link(insText, aTag, eTag) {
    var url = new RegExp('^(http://|https://|www.|ftp://|news:|mailto:).');
    var www = new RegExp('^(www.).');
    var mail = new RegExp('^[^@]+@[^@]+\.[a-zA-Z]+$');
    var http = new RegExp('^(http://)$');
    var node, href;
    if((url.test(insText)) || (mail.test(insText))) {
        href = insText;
        if (mail.test(href)) { href = 'mailto:' + insText; }
        if (www.test(href)) { href = 'http://' + href; }
        node = prompt(jlog_l_comments_url_node);
        if((node !== null) && (node !== '')) { insText = '[url=' + href + ']' + node + eTag; }
        else if(node === '') { insText = aTag + href + eTag; }
    }
    else {
        node = insText;
        if(node === '') { node = prompt(jlog_l_comments_url_node, insText); }
        href = prompt(jlog_l_comments_url_href, 'http://');
        if (http.test(href)) { return insText; }
        if (www.test(href)) { href = 'http://' + href; }
        if(((node !== null) && (node !== '')) && ((href !== null) && (href !== ''))) {
            insText = '[url=' + href + ']' + node + eTag;            
        }
        else if((href !== null) && (href !== '')) { insText = aTag + href + eTag; }
    }
    return insText;
}


function jlog_bbcode_list(o_insText, aTag, eTag) {
    var insText = o_insText.replace(/(\n|\r|\r\n)(?=(.+))/g, '$1[*]');
    return '[list]\n[*]' + insText + eTag + '\n';
}

function jlog_bbcode_insert(aTag, eTag, completeText) {
  var input = document.forms.entryform.elements.content;
  input.focus();
  var insText;
  /* für Internet Explorer und Opera >= 8 */
  if(typeof document.selection != 'undefined') {
    /* Einfügen des Formatierungscodes */
    var range = document.selection.createRange();
    insText = range.text;
    if (aTag === '[url]') { range.text = jlog_bbcode_link(insText, aTag, eTag); }
    else if(eTag === '[/list]') { range.text = jlog_bbcode_list(insText, aTag, eTag); }
    else { range.text = jlog_bbcode(insText, aTag, eTag); }
    
    /* Anpassen der Cursorposition */
    range = document.selection.createRange();
    if (insText.length === 0) {
      range.move('character', -eTag.length);
    } else {
      range.moveStart('character', insText.length);      
    }
    range.select();
  }
  /* für neuere auf Gecko basierende Browser */
  else if(typeof input.selectionStart != 'undefined')
  {
    /* Einfügen des Formatierungscodes */
    var start = input.selectionStart;
    var end = input.selectionEnd;
    insText = input.value.substring(start, end);
    if(aTag === '[url]') { insText = jlog_bbcode_link(insText, aTag, eTag); }
    else if(eTag === '[/list]') { insText = jlog_bbcode_list(insText, aTag, eTag); }
    else { insText = jlog_bbcode(insText, aTag, eTag); }
    
     input.value = input.value.substr(0, start) + insText  + input.value.substr(end);
    
    /* Anpassen der Cursorposition */
    var pos;
    if (insText.length === 0) {
      pos = start + aTag.length + eTag.length;
    } else {
      pos = start + insText.length;
    }
    input.selectionStart = pos;
    input.selectionEnd = pos;
  }
  /* für die übrigen Browser */
  else
  {
    /* Einfügen des Formatierungscodes */
    if(aTag === '[url]') { insText = jlog_bbcode_link('', aTag, eTag); }
    else if(eTag === '[/list]') { insText = jlog_bbcode_list('', aTag, eTag); }
    else { insText = jlog_bbcode(prompt(jlog_l_comments_plz_format_txt), aTag, eTag); }
    input.value += insText;
  }
}

function jlog_bbcode_img(jfilename) {
    var jclass = '';
    var jalt = '';
    if ( document.getElementById("class").value !== '') {
        jclass = ' class=\"' + document.getElementById("class").value + '\"';
    }
    if ( document.getElementById("alt").value !== '') {
        jalt = ' alt=\"' + document.getElementById("alt").value + '\"';
    }
    var jimg = '[img' + jclass + jalt + ']' + jfilename + '[/img]';
    opener.parent.jlog_insertAtCursor(jimg);
    window.close();
}

// from http://www.alexking.org/blog/2003/06/02/inserting-at-the-cursor-using-javascript/
function jlog_insertAtCursor(insText) {
  //IE and Opera support
  var field = document.forms.entryform.elements.content;
  if (document.selection) {
    field.focus();
    var sel = document.selection.createRange();
    sel.text = insText;
  }
  //MOZILLA/NETSCAPE support
  else if (field.selectionStart || field.selectionStart == '0') {
    var startPos = field.selectionStart;
    var endPos = field.selectionEnd;
    field.value = field.value.substring(0, startPos) + insText + field.value.substring(endPos, field.value.length);
  } else {
    field.value += insText;
  }
}

var show = true;

function jlog_killcomments() {

    var commentslist = document.getElementById("commentslist");
    var pingbacks_header = document.getElementById("pingbacks");
    var pingbacks_list = document.getElementById("pingbackslist");

    if (show) {
        document.getElementById("hidecomments").firstChild.nodeValue = jlog_l_comments_show;
        show=false;
        if(pingbacks_header) { pingbacks_header.style.display = "none"; }
        if(pingbacks_list) { pingbacks_list.style.display = "none"; }
        document.getElementById("comments").style.display = "none";
        document.getElementById("entryform").style.display = "none";
        if(commentslist) { commentslist.style.display = "none"; }
    }
    else {
        document.getElementById("hidecomments").firstChild.nodeValue = jlog_l_comments_hide;
        show=true;
        if(pingbacks_header) { pingbacks_header.style.display = "block"; }
        if(pingbacks_list) { pingbacks_list.style.display = "block"; }
        document.getElementById("comments").style.display = "block";
        document.getElementById("entryform").style.display = "block";
        if(commentslist) { commentslist.style.display = "block"; }
    }
}

var jlog_bbcode_br;

function jlog_bbcode_do_button(titel, aTag, eTag) {
    var button = document.createElement("input");
    button.onclick = function() {
        jlog_bbcode_insert(aTag, eTag);
        return false;
    };
    button.className = "jlog_bbcode";
    button.type = "button";
    button.value = titel;
    jlog_bbcode_br.parentNode.insertBefore(button, jlog_bbcode_br);
}

/* from http://www.kryogenix.org/code/browser/searchhi/ */
function jlog_highlightWord(node,word) {

    if (node.hasChildNodes) {
        for (var hi_cn=0;hi_cn<node.childNodes.length;hi_cn++) {
            jlog_highlightWord(node.childNodes[hi_cn],word);
        }
    }

    if (node.nodeType == 3) {
        var tempNodeVal = node.nodeValue.toLowerCase();
        var tempWordVal = word.toLowerCase();
        if (tempNodeVal.indexOf(tempWordVal) != -1) {
            var pn = node.parentNode;
            if (pn.className != "searchword") {
                var nv = node.nodeValue;
                var ni = tempNodeVal.indexOf(tempWordVal);
                var before = document.createTextNode(nv.substr(0,ni));
                var docWordVal = nv.substr(ni,word.length);
                var after = document.createTextNode(nv.substr(ni+word.length));
                var hiwordtext = document.createTextNode(docWordVal);
                var hiword = document.createElement("span");
                hiword.className = "searchword";
                hiword.appendChild(hiwordtext);
                pn.insertBefore(before,node);
                pn.insertBefore(hiword,node);
                pn.insertBefore(after,node);
                pn.removeChild(node);
            }
        }
    }
}

function jlog_searchengineSearchHighlight() {
    if (!document.createElement) { return; }
    var ref = document.referrer;
    if (ref.indexOf('?') == -1) { return; }
    var qs = ref.substr(ref.indexOf('?')+1);
    var qsa = qs.split('&');
    for (var i=0;i<qsa.length;i++) {
        var qsip = qsa[i].split('=');
        if (qsip.length == 1) { continue; }
        if (qsip[0] == 'q' || qsip[0] == 'p' ) { // q= for Google, p= for Yahoo
            var words = unescape(qsip[1].replace(/\+/g,' ')).split(/\s+/);
            for (var w=0;w<words.length;w++) {
                jlog_highlightWord(document.getElementsByTagName("body")[0],words[w]);
            }
        }
    }
}

function jlog_init() {
    var jlog_comments;
    if(document.getElementById("pingbacks")) { jlog_comments = document.getElementById("pingbacks"); }
    else { jlog_comments = document.getElementById("comments"); }
    if (jlog_comments) {
        if (!document.getElementById || !document.createElement || !document.createTextNode) { return; }
        var p = document.createElement("p");
        p.className = "hidecomments";
        var a = document.createElement("a");
        a.id = "hidecomments";
        a.href = "#";
        a.onclick = function() {jlog_killcomments(); return false; };
        var text = document.createTextNode(jlog_l_comments_hide);
        a.appendChild(text);
        p.appendChild(a);
        if (jlog_comments.insertBefore) {
            jlog_comments.parentNode.insertBefore(p, jlog_comments);
        }
    }

    jlog_bbcode_br = document.getElementById("bbcode");
    if(jlog_bbcode_br || (typeof(jlog_admin) !== "undefined")) {
    
        if (jlog_bbcode_br.insertBefore) {
            jlog_bbcode_do_button(jlog_l_comments_url, '[url]', '[/url]');
            jlog_bbcode_do_button(jlog_l_comments_bold, '[b]', '[/b]');
            jlog_bbcode_do_button(jlog_l_comments_italic, '[i]', '[/i]');
            jlog_bbcode_do_button(jlog_l_comments_quote, '[quote]', '[/quote]');
            if (jlog_comments) { jlog_bbcode_br.parentNode.getElementsByTagName("span")[0].style.display = "none"; }
        }

        if (typeof(jlog_admin) !== "undefined") {
            document.getElementById("jlogteaserpic").style.display = "block";
            if (jlog_bbcode_br.insertBefore) {
                jlog_bbcode_do_button(jlog_l_headline, '[headline]', '[/headline]');
                jlog_bbcode_do_button(jlog_l_list, '[list][*]', '[/list]');
            }
        }
    }
    
    if(typeof(jlog_searchpage) === "undefined") { jlog_searchengineSearchHighlight(); }
}

function addLoadEvent(func) {
  var oldonload = window.onload;
  if (typeof window.onload !== 'function') {
    window.onload = func;
  } else {
    window.onload = function() {
      oldonload();
      func();
    };
  }
}

addLoadEvent(jlog_init);
addLoadEvent( function() {
	if(document.getElementById("password")) {
		document.getElementById("password").focus();
	}
});

var winpops;

/* Open popup to learn BBCode for comments */
function jlog_learnbb(path) {
 var popurl = path + '/learn_bb.php?v=small';
 winpops=window.open(popurl,'','width=400,height=300,scrollbars=yes');
}

/* Open popup to upload pictures in admincenter */
function jlog_wopen(popurl) {
 winpops=window.open(popurl,'','width=350,height=350,scrollbars=yes');
}

function jlog_generate_url(topic, destination) {

    if ( typeof( destination ) == 'string' ) { destination = document.getElementById( destination ); }
    if ( destination.value !== '' ) { return false; }

    var url = topic.toLowerCase();
    while(url.search(/ä/) != -1) { url = url.replace(/ä/, "ae"); }
    while(url.search(/ö/) != -1) { url = url.replace(/ö/, "oe"); }
    while(url.search(/ü/) != -1) { url = url.replace(/ü/, "ue"); }
    while(url.search(/ß/) != -1) { url = url.replace(/ß/, "ss"); }
    while(url.search(/ /) != -1) { url = url.replace(/ /, "-"); }
    while(url.search(/[^a-z0-9.,_\/-]/) != -1) { url = url.replace(/[^a-z0-9.,_/-]/, ""); }

    destination.value = url;
}

/* URL fill out helper */
addLoadEvent(
    function() {
        var topic = document.getElementById('topic')
        if ( topic ) {
            topic.onchange = function() { jlog_generate_url( this.value, 'url' ); }
        }

        var namefield = document.getElementById('name');
        if( !document.getElementById('url') || !namefield ) { return; }
        else namefield.onchange = function() { jlog_generate_url( this.value, 'url' ); }
    }
)
