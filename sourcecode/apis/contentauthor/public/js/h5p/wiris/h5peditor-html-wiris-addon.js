var H5PEditor = H5PEditor || {};
H5PEditor.HtmlAddons = H5PEditor.HtmlAddons || {};
H5PEditor.HtmlAddons.math = H5PEditor.HtmlAddons.math || {};
H5PEditor.HtmlAddons.math.ndla_wiris = function (config, tags) {
  // Add the plugin.
  if (config.extraPlugins) {
    config.extraPlugins += ',ckeditor_wiris';
  } else {
    config.extraPlugins = 'ckeditor_wiris';
  }

  // Add plugin to toolbar.
  config.toolbar.push({
    name: "ndla_wiris",
    items: ['ckeditor_wiris_formulaEditor']
  });

  // We need to support quite few more tags now.
  tags.push('span'); // For the containing span.WirisFormula
  tags.push('math');
  tags.push('maction');
  tags.push('maligngroup');
  tags.push('malignmark');
  tags.push('menclose');
  tags.push('merror');
  tags.push('mfenced');
  tags.push('mfrac');
  tags.push('mglyph');
  tags.push('mi');
  tags.push('mlabeledtr');
  tags.push('mlongdiv');
  tags.push('mmultiscripts');
  tags.push('mn');
  tags.push('mo');
  tags.push('mover');
  tags.push('mpadded');
  tags.push('mphantom');
  tags.push('mroot');
  tags.push('mrow');
  tags.push('ms');
  tags.push('mscarries');
  tags.push('mscarry');
  tags.push('msgroup');
  tags.push('msline');
  tags.push('mspace');
  tags.push('msqrt');
  tags.push('msrow');
  tags.push('mstack');
  tags.push('mstyle');
  tags.push('msub');
  tags.push('msup');
  tags.push('msubsup');
  tags.push('mtable');
  tags.push('mtd');
  tags.push('mtext');
  tags.push('mtr');
  tags.push('munder');
  tags.push('munderover');
  // Unsure if these are necessary?
  tags.push('semantics');
  tags.push('annotation');
  tags.push('annotation-xml');
};

