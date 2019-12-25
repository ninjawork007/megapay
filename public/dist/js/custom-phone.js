function(o) {
  if (/(38|40|27|32)/.test(o.which) && !/input|textarea/i.test(o.target.tagName)) {
    var i = t(this);
    if (o.preventDefault(), o.stopPropagation(), !i.is(".disabled, :disabled")) {
      var n = e(i),
        s = n.hasClass("open");
      if (!s && 27 != o.which || s && 27 == o.which) return 27 == o.which && n.find(r).trigger("focus"), i.trigger("click");
      var a = " li:not(.disabled):visible a",
        p = n.find(".dropdown-menu" + a);
      if (p.length) {
        var l = p.index(o.target);
        38 == o.which && l > 0 && l--, 40 == o.which && l < p.length - 1 && l++, ~l || (l = 0), p.eq(l).trigger("focus")
      }
    }
  }
}