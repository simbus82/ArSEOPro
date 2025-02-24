<div class="row">
    <div class="col-sm-8">
        <ul class="nav nav-tabs" style="font-size: 14px">
            <li class="active">
                <a href="#arseo-help-url-settings" id="arseo-help-url-settings-tab" data-toggle="tab">
                    URL settings
                </a>
            </li>
            <li class="">
                <a href="#arseo-help-favicon-settings" id="arseo-help-favicon-settings-tab" data-toggle="tab">
                    Favicon settings
                </a>
            </li>
            <li class="">
                <a href="#arseo-help-meta-settings" id="arseo-help-meta-settings-tab" data-toggle="tab">
                    Meta tags
                </a>
            </li>
            <li class="">
                <a href="#arseo-help-redirect-settings" id="arseo-help-redirect-settings-tab" data-toggle="tab">
                    Redirects
                </a>
            </li>
            <li class="">
                <a href="#arseo-help-sitemap-settings" id="arseo-help-sitemap-settings-tab" data-toggle="tab">
                    Sitemap
                </a>
            </li>
            <li class="">
                <a href="#arseo-help-robots-settings" id="arseo-help-robots-settings-tab" data-toggle="tab">
                    Robots editor
                </a>
            </li>
            <li class="">
                <a href="#arseo-help-faq-settings" id="arseo-help-faq-settings-tab" data-toggle="tab">
                    FAQ
                </a>
            </li>
        </ul>
        <div class="tab-content">
            {include file="./_tabs/url.tpl"}
            {include file="./_tabs/favicon.tpl"}
            {include file="./_tabs/meta.tpl"}
            {include file="./_tabs/redirect.tpl"}
            {include file="./_tabs/sitemap.tpl"}
            {include file="./_tabs/robots.tpl"}
            {include file="./_tabs/faq.tpl"}
        </div>
    </div>
    <div class="col-sm-4">
        {include file="./menu.tpl"}
    </div>
</div>