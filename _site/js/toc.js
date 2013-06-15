;(function generate_toc() {
  var headers = $('h1, h2, h3, h4, h5, h6').filter(function() {return this.id;});
  var output = $('.toc');
  if (!headers.length || headers.length < 3 || !output.length)
    return;

  var get_level = function(ele) {
    return parseInt(ele.nodeName.replace("H", ""), 10);
  }

  var level = get_level(headers[0]); // get the initial level
  var this_level;

  var html = "<h1>Table of contents</h1> <ol>";
  headers.each(function(_, header) {
    this_level = get_level(header);
    if (this_level === level) // same level as before; same indenting
      html += "<li><a href='#" + header.id + "'>" + header.innerHTML + "</a>";
    else if (this_level < level) // higher level than before; end parent ol
      html += "</li></ol></li><li><a href='#" + header.id + "'>" + header.innerHTML + "</a>";
    else if (this_level > level) // lower level than before; expand the previous to contain a ol
      html += "<ol><li><a href='#" + header.id + "'>" + header.innerHTML + "</a>";
    level = this_level; // update for the next one
  });
  html += "</ol>";
  output.html(html);
  output.removeClass('hide');
})()
