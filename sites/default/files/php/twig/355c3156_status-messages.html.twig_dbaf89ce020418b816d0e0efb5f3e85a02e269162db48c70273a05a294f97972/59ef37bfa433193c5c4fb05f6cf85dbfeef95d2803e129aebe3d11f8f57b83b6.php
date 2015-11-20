<?php

/* @classy/misc/status-messages.html.twig */
class __TwigTemplate_1623435ec433e313c4354baf8c83a4c51fddf84da63561d8a91afa006427b335 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'messages' => array($this, 'block_messages'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $tags = array("block" => 27, "for" => 28, "set" => 30, "if" => 36);
        $filters = array("without" => 35, "length" => 42, "first" => 49);
        $functions = array("attach_library" => 26);

        try {
            $this->env->getExtension('sandbox')->checkSecurity(
                array('block', 'for', 'set', 'if'),
                array('without', 'length', 'first'),
                array('attach_library')
            );
        } catch (Twig_Sandbox_SecurityError $e) {
            $e->setTemplateFile($this->getTemplateName());

            if ($e instanceof Twig_Sandbox_SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof Twig_Sandbox_SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof Twig_Sandbox_SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

        // line 26
        echo $this->env->getExtension('sandbox')->ensureToStringAllowed($this->env->getExtension('drupal_core')->escapeFilter($this->env, $this->env->getExtension('drupal_core')->attachLibrary("classy/messages"), "html", null, true));
        echo "
";
        // line 27
        $this->displayBlock('messages', $context, $blocks);
    }

    public function block_messages($context, array $blocks = array())
    {
        // line 28
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["message_list"]) ? $context["message_list"] : null));
        foreach ($context['_seq'] as $context["type"] => $context["messages"]) {
            // line 29
            echo "  ";
            // line 30
            $context["classes"] = array(0 => "messages", 1 => ("messages--" .             // line 32
$context["type"]));
            // line 35
            echo "  <div role=\"contentinfo\" aria-label=\"";
            echo $this->env->getExtension('sandbox')->ensureToStringAllowed($this->env->getExtension('drupal_core')->escapeFilter($this->env, $this->getAttribute((isset($context["status_headings"]) ? $context["status_headings"] : null), $context["type"], array(), "array"), "html", null, true));
            echo "\"";
            echo $this->env->getExtension('sandbox')->ensureToStringAllowed($this->env->getExtension('drupal_core')->escapeFilter($this->env, twig_without($this->getAttribute((isset($context["attributes"]) ? $context["attributes"] : null), "addClass", array(0 => (isset($context["classes"]) ? $context["classes"] : null)), "method"), "role", "aria-label"), "html", null, true));
            echo ">
    ";
            // line 36
            if (($context["type"] == "error")) {
                // line 37
                echo "      <div role=\"alert\">
    ";
            }
            // line 39
            echo "      ";
            if ($this->getAttribute((isset($context["status_headings"]) ? $context["status_headings"] : null), $context["type"], array(), "array")) {
                // line 40
                echo "        <h2 class=\"visually-hidden\">";
                echo $this->env->getExtension('sandbox')->ensureToStringAllowed($this->env->getExtension('drupal_core')->escapeFilter($this->env, $this->getAttribute((isset($context["status_headings"]) ? $context["status_headings"] : null), $context["type"], array(), "array"), "html", null, true));
                echo "</h2>
      ";
            }
            // line 42
            echo "      ";
            if ((twig_length_filter($this->env, $context["messages"]) > 1)) {
                // line 43
                echo "        <ul class=\"messages__list\">
          ";
                // line 44
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable($context["messages"]);
                foreach ($context['_seq'] as $context["_key"] => $context["message"]) {
                    // line 45
                    echo "            <li class=\"messages__item\">";
                    echo $this->env->getExtension('sandbox')->ensureToStringAllowed($this->env->getExtension('drupal_core')->escapeFilter($this->env, $context["message"], "html", null, true));
                    echo "</li>
          ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['message'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 47
                echo "        </ul>
      ";
            } else {
                // line 49
                echo "        ";
                echo $this->env->getExtension('sandbox')->ensureToStringAllowed($this->env->getExtension('drupal_core')->escapeFilter($this->env, twig_first($this->env, $context["messages"]), "html", null, true));
                echo "
      ";
            }
            // line 51
            echo "    ";
            if (($context["type"] == "error")) {
                // line 52
                echo "      </div>
    ";
            }
            // line 54
            echo "  </div>
  ";
            // line 56
            echo "  ";
            echo $this->env->getExtension('sandbox')->ensureToStringAllowed($this->env->getExtension('drupal_core')->escapeFilter($this->env, $this->getAttribute((isset($context["attributes"]) ? $context["attributes"] : null), "removeClass", array(0 => (isset($context["classes"]) ? $context["classes"] : null)), "method"), "html", null, true));
            echo "
";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['type'], $context['messages'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
    }

    public function getTemplateName()
    {
        return "@classy/misc/status-messages.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  124 => 56,  121 => 54,  117 => 52,  114 => 51,  108 => 49,  104 => 47,  95 => 45,  91 => 44,  88 => 43,  85 => 42,  79 => 40,  76 => 39,  72 => 37,  70 => 36,  63 => 35,  61 => 32,  60 => 30,  58 => 29,  54 => 28,  48 => 27,  44 => 26,);
    }
}
/* {#*/
/* /***/
/*  * @file*/
/*  * Theme override for status messages.*/
/*  **/
/*  * Displays status, error, and warning messages, grouped by type.*/
/*  **/
/*  * An invisible heading identifies the messages for assistive technology.*/
/*  * Sighted users see a colored box. See http://www.w3.org/TR/WCAG-TECHS/H69.html*/
/*  * for info.*/
/*  **/
/*  * Add an ARIA label to the contentinfo area so that assistive technology*/
/*  * user agents will better describe this landmark.*/
/*  **/
/*  * Available variables:*/
/*  * - message_list: List of messages to be displayed, grouped by type.*/
/*  * - status_headings: List of all status types.*/
/*  * - display: (optional) May have a value of 'status' or 'error' when only*/
/*  *   displaying messages of that specific type.*/
/*  * - attributes: HTML attributes for the element, including:*/
/*  *   - class: HTML classes.*/
/*  **/
/*  * @see template_preprocess_status_messages()*/
/*  *//* */
/* #}*/
/* {{ attach_library('classy/messages') }}*/
/* {% block messages %}*/
/* {% for type, messages in message_list %}*/
/*   {%*/
/*     set classes = [*/
/*       'messages',*/
/*       'messages--' ~ type,*/
/*     ]*/
/*   %}*/
/*   <div role="contentinfo" aria-label="{{ status_headings[type] }}"{{ attributes.addClass(classes)|without('role', 'aria-label') }}>*/
/*     {% if type == 'error' %}*/
/*       <div role="alert">*/
/*     {% endif %}*/
/*       {% if status_headings[type] %}*/
/*         <h2 class="visually-hidden">{{ status_headings[type] }}</h2>*/
/*       {% endif %}*/
/*       {% if messages|length > 1 %}*/
/*         <ul class="messages__list">*/
/*           {% for message in messages %}*/
/*             <li class="messages__item">{{ message }}</li>*/
/*           {% endfor %}*/
/*         </ul>*/
/*       {% else %}*/
/*         {{ messages|first }}*/
/*       {% endif %}*/
/*     {% if type == 'error' %}*/
/*       </div>*/
/*     {% endif %}*/
/*   </div>*/
/*   {# Remove type specific classes. #}*/
/*   {{ attributes.removeClass(classes) }}*/
/* {% endfor %}*/
/* {% endblock messages %}*/
/* */
