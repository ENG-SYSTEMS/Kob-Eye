<?php

/* Skins/TestTwig/Default.twig */
class __TwigTemplate_0ba50d334ca5862ec3dda057e7d6c04e9e6e2d41fe78cf94176364a27cb7c6a0 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo twig_include($this->env, $context, twig_template_from_string($this->env, KeTwig::callModule("Boutique/Produit/MajPanier")));
        echo "

<div id=\"page\" class=\"clearfix\">
    ";
        // line 4
        echo twig_include($this->env, $context, twig_template_from_string($this->env, KeTwig::callModule("Systeme/Header")));
        echo "
    <div class=\"container\">
        [DATA]
    </div>
    ";
        // line 8
        echo twig_include($this->env, $context, twig_template_from_string($this->env, KeTwig::callModule("Systeme/Footer")));
        echo "
</div>
<script src='/Skins/";
        // line 10
        echo twig_escape_filter($this->env, (isset($context["currentSkin"]) ? $context["currentSkin"] : null), "html", null, true);
        echo "/Js/lightbox.min.js'></script>
<script src='/Tools/Css/Bootstrap/3.0/js/bootstrap.min.js'></script>
<script src='/Tools/Js/Colorbox/jquery.colorbox-min.js'></script>
<link rel=\"stylesheet\" href=\"/Tools/Js/Colorbox/example1/colorbox.css\" type=\"text/css\">
<script type=\"text/javascript\" src=\"/Skins/BoutiqueDefault-3.0.ENG/Js/toastr.js\"></script>


<!--[if lt IE 9]>
<script src=\"http://html5shim.googlecode.com/svn/trunk/html5.js\"></script>
<![endif]-->


<script type=\"text/javascript\">
    var classBody = \".png\";
    \$(\"body\").addClass(classBody.replace(/\\.\\w+\$/, \"\"));

    /* desactivation des liens */
    /*\$('a').each(function (index,item){
     \$(item).attr('a','#nogo');
     \$(item).click(function (e){
     e.preventDefault();
     alert('les liens sont désactivés. Il ne s\\'agit que d\\'une maquette de démonstration.');
     });
     });*/

</script>
<div class=\"modal fade\" id=\"lemodal\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"inputModalLabel\" aria-hidden=\"true\">
    <div class=\"modal-dialog\">
        <div class=\"modal-content\">
            <div class=\"modal-header\">
                <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button>
                <h4 class=\"modal-title\"></h4>
            </div>
            <div class=\"modal-body\">
            </div>
            <div class=\"modal-footer\">
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class=\"modal fade\" id=\"lemodal\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"inputModalLabel\" aria-hidden=\"true\">
    <div class=\"modal-header\">
        <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-hidden=\"true\">×</button>
        <h3 id=\"myModalLabel\"></h3>
    </div>
    <div class=\"modal-body\">
        <div class=\"modal-content\">
        </div>
    </div>
</div>


<script type=\"text/javascript\">
    /**
     * supression du contenu lors de la disparition du popup
     */
    \$('#lemodal').on('hidden.bs.modal', function (e) {
        \$(e.target).removeData('bs.modal').find('.modal-content').empty();
    });
</script>

<link rel=\"stylesheet\" href=\"/Tools/Js/animateIt/css/animations.css\" type=\"text/css\">
<link rel=\"stylesheet\" href=\"http://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css\">
<link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />

<!-- Piwik -->
<script type=\"text/javascript\">
    var _paq = _paq || [];
    _paq.push([\"setDomains\", [\"*.www.pharmaducours.fr\",\"*.www.pharmacieducours.fr\"]]);
    _paq.push(['trackPageView']);
    _paq.push(['enableLinkTracking']);
    (function() {
        var u=\"http://piwik.enguer.com/\";
        _paq.push(['setTrackerUrl', u+'piwik.php']);
        _paq.push(['setSiteId', 12]);
        var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
        g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
    })();
</script>
<noscript><p><img src=\"http://piwik.enguer.com/piwik.php?idsite=12\" style=\"border:0;\" alt=\"\" /></p></noscript>
<!-- End Piwik Code -->
<script>
    \$(document).delegate('*[class=\"thickbox\"]', 'click', function(event) {
        event.preventDefault();
        \$(this).ekkoLightbox();
    });
</script>
<script type=\"application/ld+json\">
{
  \"@context\" : \"http://schema.org\",
  \"@type\" : \"Organization\",
  \"name\" : \"Pharmacie du cours\",
  \"url\" : \"http://www.pharmacieducours.fr\",
  \"sameAs\" : [
    \"https://www.facebook.com/Pharmacie-du-Cours-454537144751780/\"
 ]
}
</script>
<script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

    ga('create', 'UA-80416355-1', 'auto');
    ga('send', 'pageview');

</script>";
    }

    public function getTemplateName()
    {
        return "Skins/TestTwig/Default.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  37 => 10,  32 => 8,  25 => 4,  19 => 1,);
    }
}
/* {{ include(template_from_string(module('Boutique/Produit/MajPanier'))) }}*/
/* */
/* <div id="page" class="clearfix">*/
/*     {{ include(template_from_string(module('Systeme/Header'))) }}*/
/*     <div class="container">*/
/*         [DATA]*/
/*     </div>*/
/*     {{ include(template_from_string(module('Systeme/Footer'))) }}*/
/* </div>*/
/* <script src='/Skins/{{ currentSkin }}/Js/lightbox.min.js'></script>*/
/* <script src='/Tools/Css/Bootstrap/3.0/js/bootstrap.min.js'></script>*/
/* <script src='/Tools/Js/Colorbox/jquery.colorbox-min.js'></script>*/
/* <link rel="stylesheet" href="/Tools/Js/Colorbox/example1/colorbox.css" type="text/css">*/
/* <script type="text/javascript" src="/Skins/BoutiqueDefault-3.0.ENG/Js/toastr.js"></script>*/
/* */
/* */
/* <!--[if lt IE 9]>*/
/* <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>*/
/* <![endif]-->*/
/* */
/* */
/* <script type="text/javascript">*/
/*     var classBody = ".png";*/
/*     $("body").addClass(classBody.replace(/\.\w+$/, ""));*/
/* */
/*     /* desactivation des liens *//* */
/*     /*$('a').each(function (index,item){*/
/*      $(item).attr('a','#nogo');*/
/*      $(item).click(function (e){*/
/*      e.preventDefault();*/
/*      alert('les liens sont désactivés. Il ne s\'agit que d\'une maquette de démonstration.');*/
/*      });*/
/*      });*//* */
/* */
/* </script>*/
/* <div class="modal fade" id="lemodal" tabindex="-1" role="dialog" aria-labelledby="inputModalLabel" aria-hidden="true">*/
/*     <div class="modal-dialog">*/
/*         <div class="modal-content">*/
/*             <div class="modal-header">*/
/*                 <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>*/
/*                 <h4 class="modal-title"></h4>*/
/*             </div>*/
/*             <div class="modal-body">*/
/*             </div>*/
/*             <div class="modal-footer">*/
/*             </div>*/
/*         </div><!-- /.modal-content -->*/
/*     </div><!-- /.modal-dialog -->*/
/* </div><!-- /.modal -->*/
/* */
/* <div class="modal fade" id="lemodal" tabindex="-1" role="dialog" aria-labelledby="inputModalLabel" aria-hidden="true">*/
/*     <div class="modal-header">*/
/*         <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>*/
/*         <h3 id="myModalLabel"></h3>*/
/*     </div>*/
/*     <div class="modal-body">*/
/*         <div class="modal-content">*/
/*         </div>*/
/*     </div>*/
/* </div>*/
/* */
/* */
/* <script type="text/javascript">*/
/*     /***/
/*      * supression du contenu lors de la disparition du popup*/
/*      *//* */
/*     $('#lemodal').on('hidden.bs.modal', function (e) {*/
/*         $(e.target).removeData('bs.modal').find('.modal-content').empty();*/
/*     });*/
/* </script>*/
/* */
/* <link rel="stylesheet" href="/Tools/Js/animateIt/css/animations.css" type="text/css">*/
/* <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">*/
/* <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />*/
/* */
/* <!-- Piwik -->*/
/* <script type="text/javascript">*/
/*     var _paq = _paq || [];*/
/*     _paq.push(["setDomains", ["*.www.pharmaducours.fr","*.www.pharmacieducours.fr"]]);*/
/*     _paq.push(['trackPageView']);*/
/*     _paq.push(['enableLinkTracking']);*/
/*     (function() {*/
/*         var u="http://piwik.enguer.com/";*/
/*         _paq.push(['setTrackerUrl', u+'piwik.php']);*/
/*         _paq.push(['setSiteId', 12]);*/
/*         var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];*/
/*         g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);*/
/*     })();*/
/* </script>*/
/* <noscript><p><img src="http://piwik.enguer.com/piwik.php?idsite=12" style="border:0;" alt="" /></p></noscript>*/
/* <!-- End Piwik Code -->*/
/* <script>*/
/*     $(document).delegate('*[class="thickbox"]', 'click', function(event) {*/
/*         event.preventDefault();*/
/*         $(this).ekkoLightbox();*/
/*     });*/
/* </script>*/
/* <script type="application/ld+json">*/
/* {*/
/*   "@context" : "http://schema.org",*/
/*   "@type" : "Organization",*/
/*   "name" : "Pharmacie du cours",*/
/*   "url" : "http://www.pharmacieducours.fr",*/
/*   "sameAs" : [*/
/*     "https://www.facebook.com/Pharmacie-du-Cours-454537144751780/"*/
/*  ]*/
/* }*/
/* </script>*/
/* <script>*/
/*     (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){*/
/*                 (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),*/
/*             m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)*/
/*     })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');*/
/* */
/*     ga('create', 'UA-80416355-1', 'auto');*/
/*     ga('send', 'pageview');*/
/* */
/* </script>*/