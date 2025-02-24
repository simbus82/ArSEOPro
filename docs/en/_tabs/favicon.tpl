<div class="tab-pane" id="arseo-help-favicon-settings">
    <div class="panel">
        <p>
            A favicon (short for favorite icon), also known as a shortcut icon, website icon, tab icon, URL icon, or bookmark icon, is a file containing icon, 
            associated with a particular website or web page.
        </p>
        <p>
            Browsers that provide favicon support typically display a page's favicon in the browser's address bar (sometimes in the history as well) and next to the page's name in a list of bookmarks.
        </p>
        <p>
            Browsers that support a tabbed document interface typically show a page's favicon next to the page's title on the tab, and site-specific browsers use the favicon as a desktop icon.
        </p>
        <p>
            Favicons can also be used to have a textless favorite site, saving space. 
        </p>
        <div class="panel-group" role="tablist" id="arseo-help-favicon-accordion" aria-multiselectable="true">
            <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="headingOne">
                    <h4 class="panel-title">
                        <a href="#arseo-help-favicon-accordion-general" role="button" data-toggle="collapse" data-parent="#arseo-help-favicon-accordion" aria-expanded="false" aria-controls="arseo-help-favicon-accordion-general" class="collapsed">
                            General settings
                        </a>
                    </h4>
                </div>
                <div class="panel-collapse collapse" role="tabpanel" id="arseo-help-favicon-accordion-general" aria-labelledby="headingOne" aria-expanded="false" style="">
                    <div class="panel-body">
                        <p>
                            Here you can set master favicon file that be used for regular favicon, HD favicon, Apple Touch Icon and Windows 8+ tiles. 
                            Minimum size is 260x260. 
                            Recomended size is 558x558. Please use PNG format only.
                        </p>
                    </div>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="headingOne">
                    <h4 class="panel-title">
                        <a href="#arseo-help-favicon-accordion-ios" role="button" data-toggle="collapse" data-parent="#arseo-help-favicon-accordion" aria-expanded="false" aria-controls="arseo-help-favicon-accordion-ios" class="collapsed">
                            iOS Safari Settings
                        </a>
                    </h4>
                </div>
                <div class="panel-collapse collapse" role="tabpanel" id="arseo-help-favicon-accordion-ios" aria-labelledby="headingOne" aria-expanded="false" style="">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <a href="{$path|escape:'htmlall':'UTF-8'}views/img/docs/{$lang|escape:'htmlall':'UTF-8'}/favicon-3.png" class="fancybox">
                                    <img class="img-responsive" src="{$path|escape:'htmlall':'UTF-8'}views/img/docs/{$lang|escape:'htmlall':'UTF-8'}/favicon-3.png" />
                                </a>
                            </div>
                            <div class="col-sm-6">
                                <p>
                                    In this section you can override icon for iOS Safari browser.
                                </p>
                                <p>
                                    If you don't want use master icon for Safari browser, disable <code>Use master image</code> option and select another PNG icon. 
                                    Minimum size is 260x260. 
                                    Recomended size is 558x558.
                                </p>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="headingOne">
                    <h4 class="panel-title">
                        <a href="#arseo-help-favicon-accordion-android" role="button" data-toggle="collapse" data-parent="#arseo-help-favicon-accordion" aria-expanded="false" aria-controls="arseo-help-favicon-accordion-android" class="collapsed">
                            Android Chrome Settings
                        </a>
                    </h4>
                </div>
                <div class="panel-collapse collapse" role="tabpanel" id="arseo-help-favicon-accordion-android" aria-labelledby="headingOne" aria-expanded="false" style="">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <a href="{$path|escape:'htmlall':'UTF-8'}views/img/docs/{$lang|escape:'htmlall':'UTF-8'}/favicon-4.png" class="fancybox">
                                    <img class="img-responsive" src="{$path|escape:'htmlall':'UTF-8'}views/img/docs/{$lang|escape:'htmlall':'UTF-8'}/favicon-4.png" />
                                </a>
                            </div>
                            <div class="col-sm-6">
                                <p>
                                    In this section you can configure settings for Android Chrome browser
                                </p>
                                <p>
                                    If you don't want use master icon for Chrome browser, disable <code>Use master image</code> option and select another PNG icon. 
                                    Minimum size is 260x260. 
                                    Recomended size is 558x558.
                                </p>
                                <p>
                                    Next options will be used to generate <a href="https://developer.mozilla.org/en-US/docs/Web/Manifest" target="_blank">manifest file</a>:
                                </p>
                                <ul>
                                    <li>
                                        <code>App name</code> - App name that be used for Android homescreen website.
                                    </li>
                                    <li>
                                        <code>App Short name</code> - short name of the app that be used for Android homescreen website.
                                    </li>
                                    <li>
                                        <code>Theme color</code> - defines the default theme color for an application. This sometimes affects how the OS displays the site (e.g., on Android's task switcher, the theme color surrounds the site).
                                    </li>
                                    <li>
                                        <code>Start URL</code> - the URL that loads when a user launches the application (e.g. when added to home screen), typically the index. Note that this has to be a relative URL, relative to the manifest url.
                                    </li>
                                    <li>
                                        <code>Orientation</code> - defines the default orientation for the website.
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="headingOne">
                    <h4 class="panel-title">
                        <a href="#arseo-help-favicon-accordion-windows" role="button" data-toggle="collapse" data-parent="#arseo-help-favicon-accordion" aria-expanded="false" aria-controls="arseo-help-favicon-accordion-windows" class="collapsed">
                            Windows Metro settings
                        </a>
                    </h4>
                </div>
                <div class="panel-collapse collapse" role="tabpanel" id="arseo-help-favicon-accordion-windows" aria-labelledby="headingOne" aria-expanded="false" style="">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <a href="{$path|escape:'htmlall':'UTF-8'}views/img/docs/{$lang|escape:'htmlall':'UTF-8'}/favicon-6.png" class="fancybox">
                                    <img class="img-responsive" src="{$path|escape:'htmlall':'UTF-8'}views/img/docs/{$lang|escape:'htmlall':'UTF-8'}/favicon-6.png" />
                                </a>
                            </div>
                            <div class="col-sm-6">
                                <p>
                                    In this section you can configure settings for Windows Metro interface
                                </p>
                                <p>
                                    If you don't want use master icon for Windows 8 and 10 tiles, disable <code>Use master image</code> option and select another PNG icon. 
                                    Minimum size is 260x260. 
                                    Recomended size is 558x558.
                                </p>
                                <p>
                                    Also you can set background for Windows 8 and 10 tiles using <code>Windows Tile Color</code>.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="headingOne">
                    <h4 class="panel-title">
                        <a href="#arseo-help-favicon-accordion-macos" role="button" data-toggle="collapse" data-parent="#arseo-help-favicon-accordion" aria-expanded="false" aria-controls="arseo-help-favicon-accordion-macos" class="collapsed">
                            MacOS Safari settings
                        </a>
                    </h4>
                </div>
                <div class="panel-collapse collapse" role="tabpanel" id="arseo-help-favicon-accordion-macos" aria-labelledby="headingOne" aria-expanded="false" style="">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <a href="{$path|escape:'htmlall':'UTF-8'}views/img/docs/{$lang|escape:'htmlall':'UTF-8'}/favicon-7.png" class="fancybox">
                                    <img class="img-responsive" src="{$path|escape:'htmlall':'UTF-8'}views/img/docs/{$lang|escape:'htmlall':'UTF-8'}/favicon-7.png" />
                                </a>
                            </div>
                            <div class="col-sm-6">
                                <p>
                                    MacOS Safari uses icon in SVG format only. So you need upload one if you want optimize favicon on MacOS.
                                </p>
                                <p>
                                    Also you can change icon color using <code>Theme color</code> field.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>