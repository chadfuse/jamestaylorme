$(".menu-item-5736").click(function () {

    // Set the effect type
    var effect = 'slide';

    // Set the options for the effect type chosen
    var options = { direction: 'right' };

    // Set the duration (default: 400 milliseconds)
    var duration = 500;

    $('#navbod').toggle(effect, options, duration);
    $('.menu-sidemenu-container').addClass('animated fadeInRightBig');
});

var myVar = setInterval(function(){ myTimer() }, 500);

function myTimer() {
    if ($('div.gallery-cell').hasClass('slide1 is-selected'))
    { 
    	$(".nt-slid.nt-slide2, .nt-slid.nt-slide3, .nt-slid.nt-slide4,.nt-slid.nt-slide5,.nt-slid.nt-slide6,.nt-slid.nt-slide7,.nt-slid.nt-slide8,.nt-slid.nt-slide9").removeClass( "opac-one" )
    	$(".nt-slid.nt-slide1").addClass( "opac-one" );
    }
    else if ($('div.gallery-cell').hasClass('slide2 is-selected')) { 
        $(".nt-slid.nt-slide1").removeClass( "opac-one" );
        $(".nt-slid.nt-slide2").addClass( "opac-one" );
    }
    else if ($('div.gallery-cell').hasClass('slide3 is-selected')) { 
        $(".nt-slid.nt-slide2").removeClass( "opac-one" );
        $(".nt-slid.nt-slide3").addClass( "opac-one" );
    }
    else if ($('div.gallery-cell').hasClass('slide4 is-selected')) { 
        $(".nt-slid.nt-slide3").removeClass( "opac-one" );
        $(".nt-slid.nt-slide4").addClass( "opac-one" );
    }
    else if ($('div.gallery-cell').hasClass('slide5 is-selected')) { 
        $(".nt-slid.nt-slide4").removeClass( "opac-one" );
        $(".nt-slid.nt-slide5").addClass( "opac-one" );
    }
     else if ($('div.gallery-cell').hasClass('slide6 is-selected')) { 
        $(".nt-slid.nt-slide5").removeClass( "opac-one" );
        $(".nt-slid.nt-slide6").addClass( "opac-one" );
    }
    else if ($('div.gallery-cell').hasClass('slide7 is-selected')) { 
        $(".nt-slid.nt-slide6").removeClass( "opac-one" );
        $(".nt-slid.nt-slide7").addClass( "opac-one" );
    }
    else if ($('div.gallery-cell').hasClass('slide8 is-selected')) { 
        $(".nt-slid.nt-slide7").removeClass( "opac-one" );
        $(".nt-slid.nt-slide8").addClass( "opac-one" );
    }
    else if ($('div.gallery-cell').hasClass('slide9 is-selected')) { 
        $(".nt-slid.nt-slide8").removeClass( "opac-one" );
        $(".nt-slid.nt-slide9").addClass( "opac-one" );
    }
}
