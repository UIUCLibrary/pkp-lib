{**
 * templates/frontend/components/footer.tpl
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @brief Common site frontend footer.
 *
 * @uses $isFullWidth bool Should this page be displayed without sidebars? This
 *       represents a page-level override, and doesn't indicate whether or not
 *       sidebars have been configured for thesite.
 *}

	</div><!-- pkp_structure_main -->

	{* Sidebars *}
	{if empty($isFullWidth)}
		{capture assign="sidebarCode"}{call_hook name="Templates::Common::Sidebar"}{/capture}
		{if $sidebarCode}
			<div class="pkp_structure_sidebar left" role="complementary">
				{$sidebarCode}
			</div><!-- pkp_sidebar.left -->
		{/if}
	{/if}
</div><!-- pkp_structure_content -->

<div class="pkp_structure_footer_wrapper" role="contentinfo">
	<a id="pkp_content_footer"></a>

	<div class="pkp_structure_footer">

		{if $pageFooter}
			<div class="pkp_footer_content">
				{$pageFooter}
			</div>
		{/if}

	</div>
                        <footer class="footer-library container" role="contentinfo">
                                <div class="row">
                                                        <div class="column footer-library_right" style="text: white">
                                                                <p>ABOUT</p>
                                                                <nav>
                                                                        <ul>
                                                                                <li><a href="https://iopn.library.illinois.edu/iopn-mission/">About IOPN</a></li>
                                                                                <li><a href="https://iopn.library.illinois.edu/advisory-board/">Advisory Board</a></li>
                                                                                <li><a href="https://iopn.library.illinois.edu/policies/">Policies</a></li>
                                                                                <li><a href="https://iopn.library.illinois.edu/news/">IOPN News</a></li>
                                                                                <li><a href="https://iopn.library.illinois.edu/contact/">Contact</a></li>
                                                                                <li><a href="https://iopn.library.illinois.edu/policies/#accessibility">Accessibility</a></li>
                                                                        </ul>
                                                                </nav>

                                                        </div>
                                                        <div class="column footer-library_right">
                                                                <p>PUBLISHING WITHOUT WALLS<p>
                                                                <nav>
                                                                        <ul>
                                                                                <li><a href="https://iopn.library.illinois.edu/publishing-without-walls/">About PWW</a></li>
                                                                                <li><a href="https://iopn.library.illinois.edu/books/pww/catalog">PWW Catalog</a></li>
                                                                                <li><a href="https://pww.afro.illinois.edu">AFRO-PWW Project</a></li>

                                                                        </ul>
                                                                </nav>
                                                        </div>
                                                        <div class="column footer-library_right">
                                                                <p>WINDSOR & DOWNS</p>
                                                                <nav>
                                                                        <ul>
                                                                                <li><a href="https://iopn.library.illinois.edu/windsor-downs-press/">About Windsor and Downs</a></li>
                                                                                <li><a href="https://iopn.library.illinois.edu/books/windsor-downs/catalog">Windsor and Downs Catalog</a></li>
                                                                        </ul>
                                                                </nav>


                                                        </div>

                                                        <div class="column footer-library_right">
                                                                <p>IOPN JOURNALS</p>
                                                                <nav>
                                                                        <ul>
                                                                                <li><a href="https://iopn.library.illinois.edu/iopn-journals/">About IOPN Journals</a></li>
                                                                                <li><a href="https://iopn.library.illinois.edu/journals/">IOPN Journals Catalog</a></li>
                                                                        </ul>
                                                                </nav>
                                                        </div>
                                </div>
                        </footer>

</div><!-- pkp_structure_footer_wrapper -->

</div><!-- pkp_structure_page -->

{load_script context="frontend"}

{call_hook name="Templates::Common::Footer::PageFooter"}
</body>
</html>
