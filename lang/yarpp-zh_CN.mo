��    f      L              |     }  
   �  q   �  �        �  ^  �     T
  $   ]
     �
     �
  "   �
  '   �
          !     *     7     U     t     �  &   �  /   �     �  *     $   =  /   b  H   �     �     �       .     n   D     �      �  #   �  !   	  1   +  1   ]  �   �          $     3     B     [     i  m        �               (  *   6     a     {     �     �  w   �  3   (  9   \  t   �  p       |  �   �  �   �     7    @  %   C     i     x  �   �  ?   {     �  �   �  5  �  (   �  :   �  #   0  .   T  e   �  j   �  )   T  I   ~     �     �     �     �     �               &     6     ?  !   H  "   j     �     �     �     �     �     �               #  �  (            m   "  �   �     7   ?  V      �"     �"     �"     �"     �"     �"     #  	   '#  	   1#     ;#     W#     g#     �#     �#     �#     �#  /   �#     $  0   $  H   M$     �$     �$  	   �$  ,   �$  4   �$     %     &%     9%     X%  -   u%  '   �%  w   �%     C&     S&     c&     s&     �&     �&  m   �&     '     7'     D'     W'  ,   g'     �'     �'  	   �'     �'  w   �'  *   ^(  6   �(  �   �(  `   T)    �)  �   �*  �   Z+  	   �+  �   �+  %   �,     �,     �,  �   -  <   �-     �-  �   �-  5  �.  #   
0  7   .0  $   f0  ;   �0  >   �0  .   1  3   51  �   i1     �1     2     2     2     (2     A2     Z2  	   ^2     h2  
   l2      w2  &   �2     �2     �2     �2     
3  !   3  !   33     U3     \3     `3   "Relatedness" options "The Pool" "The Pool" refers to the pool of posts and pages that are candidates for display as related to the current entry. %f is the YARPP match score between the current entry and this related entry. You are seeing this value because you are logged in to WordPress as an administrator. It is not shown to regular visitors. (Update options to reload.) <h3>An important message from YARPP:</h3><p>Thank you for upgrading to YARPP 2. YARPP 2.0 adds the much requested ability to limit related entry results by certain tags or categories. 2.0 also brings more fine tuned control of the magic algorithm, letting you specify how the algorithm should consider or not consider entry content, titles, tags, and categories. Make sure to adjust the new settings to your liking and perhaps readjust your threshold.</p><p>For more information, check out the <a href="http://mitcho.com/code/yarpp/">YARPP documentation</a>. (This message will not be displayed again.)</p> Advanced Automatically display related posts? Before / after (Excerpt): Before / after (excerpt): Before / after each related entry: Before / after related entries display: Before / after related entries: Bodies:  Categories:  Cross-relate posts and pages? Default display if no results: Disallow by category: Disallow by tag: Display options <small>for RSS</small> Display options <small>for your website</small> Display related posts in feeds? Display related posts in the descriptions? Display using a custom template file Do you really want to reset your configuration? Donate to mitcho (Michael Yoshitaka Erlewine) for this plugin via PayPal Example post  Excerpt length (No. of words): For example: Help promote Yet Another Related Posts Plugin? If, despite this check, you are sure that <code>%s</code> is using the MyISAM engine, press this magic button: Match threshold: Maximum number of related posts: MySQL error on adding yarpp_content MySQL error on adding yarpp_title MySQL error on creating yarpp_keyword_cache table MySQL error on creating yarpp_related_cache table No YARPP template files were found (in <code>wp-content/yarpp-templates</code>) and so the templating feature has been turned off. No related posts. Options saved! Order results: RSS display code example Related Posts Related Posts (YARPP) Related posts brought to you by <a href='http://mitcho.com/code/yarpp/'>Yet Another Related Posts Plugin</a>. Related posts cache status Reset options Show cache status Show excerpt? Show only posts from the past NUMBER UNITS Show only previous posts? Show password protected posts? Tags:  Template file: The MyISAM check has been overridden. You may now use the "consider titles" and "consider bodies" relatedness criteria. The YARPP database had an error but has been fixed. The YARPP database has an error which could not be fixed. There is a new beta (VERSION) of Yet Another Related Posts Plugin. You can <A>download it here</a> at your own risk. There is a new version (VERSION) of Yet Another Related Posts Plugin available! You can <A>download it here</a>. This option automatically displays related posts right after the content on single entry pages. If this option is off, you will need to manually insert <code>related_posts()</code> or variants (<code>related_pages()</code> and <code>related_entries()</code>) into your theme files. This option displays the related posts in the RSS description fields, not just the content. If your feeds are set up to only display excerpts, however, only the description field is used, so this option is required for any display at all. This option will add the code %s. Try turning it on, updating your options, and see the code in the code example to the right. These links and donations are greatly appreciated. Titles:  To restore these features, please update your <code>%s</code> table by executing the following SQL directive: <code>ALTER TABLE `%s` ENGINE = MyISAM;</code> . No data will be erased by altering the table's engine, although there are performance implications. Trust me. Let me use MyISAM features. Update options Website display code example When the "Cross-relate posts and pages" option is selected, the <code>related_posts()</code>, <code>related_pages()</code>, and <code>related_entries()</code> all will give the same output, returning both related pages and posts. When the cache is incomplete, compute related posts on the fly? YARPP YARPP is different than the <a href="http://wasabi.pbwiki.com/Related%20Entries">previous plugins it is based on</a> as it limits the related posts list by (1) a maximum number and (2) a <em>match threshold</em>. YARPP's "consider titles" and "consider bodies" relatedness criteria require your <code>%s</code> table to use the <a href='http://dev.mysql.com/doc/refman/5.0/en/storage-engines.html'>MyISAM storage engine</a>, but the table seems to be using the <code>%s</code> engine. These two options have been disabled. Yet Another Related Posts Plugin Options Yet Another Related Posts Plugin version history (RSS 2.0) You cannot rebuild the YARPP cache. Your related posts cache is PERCENT% complete. Your related posts cache is empty. Please build your cache from the <A>related posts status pane</a>. Your related posts cache is incomplete. Please build your cache from the <A>related posts status pane</a>. Your related posts cache is now complete. by <a href="http://mitcho.com/code/">mitcho (Michael 芳貴 Erlewine)</a> category close consider consider with extra weight date (new to old) date (old to new) day(s) do not consider month(s) more&gt; require at least one %s in common require more than one %s in common score (high relevance to low) score (low relevance to high) starting... tag title (alphabetical) title (reverse alphabetical) try to continue week(s) word Project-Id-Version: Yet Another Related Posts Plugin v2.1.3
PO-Revision-Date: 2008-11-10 08:40-0600
Last-Translator: Jor <mail@jorwang.com>
MIME-Version: 1.0
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit
Plural-Forms: nplurals=2; plural=1;
X-Poedit-Language: Chinese
X-Poedit-Country: PEOPLE'S REPUBLIC OF CHINA
X-Poedit-SourceCharset: utf-8
X-Poedit-KeywordsList: __;_e;__ngettext:1,2;__ngettext_noop:1,2;_c
X-Poedit-Basepath: 
X-Poedit-SearchPath-0: . 关联设置 全局设置 您可以在“全局设置”里排除关联某些分类或标签，使其 YARPP 不再关联显示它们。 YARPP 中的 %f 是指当前文章和关联文章之间的匹配程度。当您以管理员的身份登录时才能查阅，游客等其他权限是不能查阅的。 （更新设置后生效。） <h3>关于 YARPP 的重要信息:</h3><p>感谢您升级到 YARPP 2. YARPP 2.0 adds the much requested ability to limit related entry results by certain tags or categories. 2.0 also brings more fine tuned control of the magic algorithm, letting you specify how the algorithm should consider or not consider entry content, titles, tags, and categories. Make sure to adjust the new settings to your liking and perhaps readjust your threshold.</p><p>更多信息，请查阅 <a href="http://mitcho.com/code/yarpp/">YARPP 官方手册</a>. (本条信息不会再次出现。)</p> 高级设置 自动插入关联文章？ 摘要起止标签： 摘要起止标签： 条目起止标签 主体起止标签： 主体起止标签 正文： 分类： 固定页面参与关联？ 无匹配时： 禁止关联以下分类： 禁止关联以下标签： RSS 相关设置 显示设置 在文章底部显示？ 在摘要中显示？(当RSS只输出摘要时) 使用自定义模板 请注意！您确定重置所有的设置吗？ 通过 PayPal 给插件作者 mitcho (Michael Yoshitaka Erlewine) 捐赠 范文 摘要字符数 例如： 帮助推广关联文章（YARPP）插件？ 同意后 <code>%s</code> 将使用 MyISAM 引擎： 匹配值： 最多关联篇数 添加 yarpp_content 时错误 添加 yarpp_title 时错误 创建 yarpp_keyword_cache table 表时错误 创建 yarpp_related_cache 表时错误 没有在（<code>wp-content/yarpp-templates</code>）下找到相应的关联文章（YARPP）自定义模板文件。 无关联文章 保存成功！ 排序方式： RSS 显示的代码示例 Related Posts 关联文章 (YARPP) Related posts brought to you by <a href='http://mitcho.com/code/yarpp/'>Yet Another Related Posts Plugin</a>. 关联文章缓存状态 重置设置 查看缓存状态 显示摘要？ 仅关联显示 NUMBER UNITS 内的文章。 只显示以前的日志？ 关联显示加密日志？ 标签： 模板文件： The MyISAM check has been overridden. You may now use the "consider titles" and "consider bodies" relatedness criteria. YARPP 数据库中的错误已被修复。 YARPP 数据库中出现一个错误，无法修复。 新的测试版本发布了！<A>点击下载</a> Yet Another Related Posts Plugin (VERSION) 。（测试版使用有风险，请注意备份。） 新的正式版本发布了！<A>点击下载</a> Yet Another Related Posts Plugin (VERSION) 。 根据右侧的代码示例，自动插入关联文章到您的页面中，当您取消该选项时，需要通过手工加入代码 <code>related_posts()</code> 或者根据需求插入 (<code>related_pages()</code> and <code>related_entries()</code>) 到您的模板文件中。 该选项将会在 RSS 的摘要区中插入关联文章，而不是在正文区中。如果您的 RSS 只输出摘要，请选择该项。 选中该项后会在模板中加入以下代码：%s. 设置更新后可以在右侧的代码示例看到。真诚地感谢您的推广或捐赠。 标题： 如果要恢复这些功能，请在您的 <code>%s</code> 表执行下列SQL指令： <code>ALTER TABLE `%s` ENGINE = MyISAM;</code> 。该操作将修改数据库，但不会损坏您的其它设置。 Trust me. Let me use MyISAM features. 保存设置 网站显示的代码示例 选中该项后，<code>related_posts()</code>, <code>related_pages()</code>, 和 <code>related_entries()</code> 这些代码的输出结果相同。 当缓存不完整时，由插件自行匹配关联文章？ YARPP 关联文章（YARPP）不同于<a href="http://wasabi.pbwiki.com/Related%20Entries">以往的关联文章插件</a>，它有着更为先进的关联算法。您可以通过设置最大显示条目和匹配值使关联更精准。 YARPP's "consider titles" and "consider bodies" relatedness criteria require your <code>%s</code> table to use the <a href='http://dev.mysql.com/doc/refman/5.0/en/storage-engines.html'>MyISAM storage engine</a>, but the table seems to be using the <code>%s</code> engine. These two options have been disabled. 关联文章（YARPP）插件设置 Yet Another Related Posts Plugin 版本历史 (RSS 2.0) 您不能建立关联文章缓存。 您的关联文章（YARPP）缓存完整率为：PERCENT%  您还尚未建立关联文章缓存， <A>立即重建</a>。 您的缓存不完整，<A>立即重建</a>。 恭喜！您的关联文章缓存已成功完成。 插件作者：<a href="http://mitcho.com/code/">mitcho (Michael 芳貴 Erlewine)</a> | 中文译者：<a href="http://jorwang.com/">JorWang</a> 分类 关闭 参考 作为主要参考指标 日期（由新到旧） 日期（由旧到新） 天 不参考 月 更多&gt; 至少从一个%s中考虑关联 至少从一个以上%s中考虑关联 匹配值（有高到低） 匹配值（由低到高） 开始建立…… 标签 标题（按字母顺序排列） 标题（按逆向字母排列） 重试 周 字符 