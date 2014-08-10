(function() {
    var original = false;
    $(".pageTitle,.pageParent")
    .on("focus",function() {
        var form = $(this).closest("form");
        var pageTitle = form.find(".pageTitle").val();
        var pageParentPath = form.find(".pageParent").find("option:selected").data("path");
        if (!pageParentPath) {
            pageParentPath = "";
        }

        var pagePath = form.find(".pagePath");
        original = pagePath.val() == (pageParentPath+"/"+convertTitleToPath(pageTitle)).replace(/\/\//g,'/') || pagePath.val() == "";
    })
    .on("change",function() {
        if (!original) {
            return;
        }
        var form = $(this).closest("form");
        var pageTitle = form.find(".pageTitle").val();
        var pageParentPath = form.find(".pageParent").find("option:selected").data("path");
        if (!pageParentPath) {
            pageParentPath = "";
        }

        var pagePath = form.find(".pagePath");
        pagePath.val((pageParentPath+"/"+convertTitleToPath(pageTitle)).replace(/\/\//g,'/')).change();
    });

    function convertTitleToPath(title) {
        return title
            .toLowerCase()
            .replace(/ /g,'-')
            .replace(/æ/g,'ae')
            .replace(/ø/g,'oe')
            .replace(/å/g,'aa')
            .replace(/[^\x00-\x7F]/g, "");
    }
})();