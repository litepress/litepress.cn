var $ = jQuery.noConflict();


$(function () {
    $(".heti h2").each(function () {
        text = $(this).text();
        $(this).attr("id", text);
    });
    tocbot.init({
        // Where to render the table of contents.
        tocSelector: '.js-toc',
        // Where to grab the headings to build the table of contents.
        contentSelector: '.entry-content ',
        // Which headings to grab inside of the contentSelector element.
        headingSelector: 'h1, h2, h3',
        // For headings inside relative or absolute positioned containers within content.
        hasInnerContainers: true,
        scrollSmooth: true,
        scrollSmoothOffset: -90,
        headingsOffset: 150
    });


    $(".heti img").each(function () {
        let src = $(this).attr("src");
        $(this).wrap("<a class=\"item\" href='" + src + "' ></a>")
    });
    lightGallery(document.getElementById('primary'), {

        selector: '.item',

    });


});



