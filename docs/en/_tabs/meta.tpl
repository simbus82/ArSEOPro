<div class="tab-pane" id="arseo-help-meta-settings">
    <div class="panel">
        <p>
            Here you can configure meta tags, Facebook OpenGraph tags and Twitter Card tags 
            for <code>products</code>, <code>cattegories</code> and meta-pages like <code>home page</code>, <code>new products</code>, <code>best sellers</code> etc...
        </p>
        <div class="panel-group" role="tablist" id="arseo-help-meta-accordion" aria-multiselectable="true">
            <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="headingOne">
                    <h4 class="panel-title">
                        <a href="#arseo-help-meta-accordion-create" role="button" data-toggle="collapse" data-parent="#arseo-help-meta-accordion" aria-expanded="false" aria-controls="arseo-help-meta-accordion-create" class="collapsed">
                            Creating new rule
                        </a>
                    </h4>
                </div>
                <div class="panel-collapse collapse" role="tabpanel" id="arseo-help-meta-accordion-create" aria-labelledby="headingOne" aria-expanded="false" style="">
                    <div class="panel-body">
                        <p>
                            <a href="{$path|escape:'htmlall':'UTF-8'}views/img/docs/{$lang|escape:'htmlall':'UTF-8'}/meta-1-1.png" class="fancybox">
                                <img class="img-responsive" src="{$path|escape:'htmlall':'UTF-8'}views/img/docs/{$lang|escape:'htmlall':'UTF-8'}/meta-1-1.png" />
                            </a>
                        </p>
                        <p>
                            To create rule for products, please click <code>New product rule</code> button at the bottom of <code>Meta tags rules</code> tab. Then fill form fields.
                        </p>
                        <div class="row">
                            <div class="col-sm-6">
                                <p>
                                    On <code>General</code> tab you can set general parameters of the rule:
                                </p>
                                <a href="{$path|escape:'htmlall':'UTF-8'}views/img/docs/{$lang|escape:'htmlall':'UTF-8'}/meta-1-2.png" class="fancybox">
                                    <img class="img-responsive" src="{$path|escape:'htmlall':'UTF-8'}views/img/docs/{$lang|escape:'htmlall':'UTF-8'}/meta-1-2.png" />
                                </a>
                            </div>
                            <div class="col-sm-6">
                                <ol>
                                    <li>
                                        <code>Rule type</code> - type of rule. This field is read-only just for the information
                                    </li>
                                    <li>
                                        <code>Rule name</code> - you can set custom rule name to easily identify the rule. You can leave this field blank for auto-generated rule name 
                                    </li>
                                    <li>
                                        <code>Language</code> - language to which the rule will apply. You can choose specific language a create rule for all languages 
                                    </li>
                                    <li>
                                        <code>Categories</code> - product categories to which the rule will apply. You can choose specific category or few categories or all categories
                                    </li>
                                </ol>
                            </div>
                        </div>
                        <hr/>
                        <div class="row">
                            <div class="col-sm-6">
                                <p>
                                    On <code>Meta</code> tab you can set meta tags using variables:
                                </p>
                                <a href="{$path|escape:'htmlall':'UTF-8'}views/img/docs/{$lang|escape:'htmlall':'UTF-8'}/meta-1-3.png" class="fancybox">
                                    <img class="img-responsive" src="{$path|escape:'htmlall':'UTF-8'}views/img/docs/{$lang|escape:'htmlall':'UTF-8'}/meta-1-3.png" />
                                </a>
                            </div>
                            <div class="col-sm-6">
                                <ol>
                                    <li>
                                        <code>Meta title</code> shows the name of a web page. The title is displayed by the browser, usually at the top of your computer screen, and tells a reader what page they are on. Meta titles are also read by search engine robots and seen by site visitors
                                    </li>
                                    <li>
                                        <code>Meta description</code> is a snippet of up to about 160 characters – a tag in HTML – which summarizes a page's content. Search engines show the meta description in search results mostly when the searched-for phrase is within the description, so optimizing the meta description is crucial for on-page SEO
                                    </li>
                                    <li>
                                        <code>Meta keywords</code> are a specific type of meta tag that appear in the HTML code of a Web page and help tell search engines what the topic of the page is. The most important thing to keep in mind when selecting or optimizing your meta keywords is to be sure that each keyword accurately reflects the content of your pages
                                    </li>
                                    <li>
                                        <code>Variables list</code> - available variables to generate content
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>