��    b      ,  �   <      H     I     N  
   d  q   o  �   �     �	  $   �	     �	     
  "   
  '   B
     j
     �
     �
     �
     �
     �
     �
       &     /   ;     k  *   �  $   �  /   �  H        T     b  Z   �     �  .   �  n        �      �     �  |   �     ;     M     \    k  #   m     �     �     �  9   �  m        v     �     �     �  *   �     �     �            w   $  3   �  9   �  �  
  t   �  p     `   ~  �   �    x  v   �  �   	  �   �     �     �    �  %   �     �     �  �     }   �  �   s  5  H  (   ~  D   �     �     �     �          +     =     D     T     ]  !   f  "   �     �     �     �     �                %    *     .!     6!     J!  �   ^!  �   �!  5   �"  7   �"     ,#     K#  -   j#  1   �#  *   �#  
   �#  
    $     $  +    $  6   L$     �$     �$  :   �$  6   �$  2   %  ;   M%  2   �%  7   �%  j   �%     _&  $   s&  ]   �&     �&  8   �&  x   8'     �'  "   �'     �'  �   �'     ~(     �(     �(  ;  �(  )   *     :*     M*     `*  H   v*  g   �*     '+     ;+     Q+     ^+  ;   ~+  B   �+  8   �+     6,     <,  �   K,  ;   �,  ?   -  �  Q-  �   /  m   �/  �   �/  �   w0  a  61  �   �2  6  '3  �   ^4      5     5  +  5  5   <6     r6     �6  �   �6  W   �7  �   �7  E  �8  !   :  D   2:  	   w:  	   �:     �:     �:     �:  	   �:     �:     �:     ;     ;     -;  )   M;  )   w;     �;     �;     �;     �;     �;         #   E       <   :      N   D   &   L   %   T   /   	   ;   b   !   )   ,           [   `       J   F           _         Y       Z            \   ?      ^      I          U             B      =   X   Q   M   2      "   5   V           W          (   0   O              $          
   6                                  9       +   .   ]   >   -   K   7       3   a          R   '       *       8               @          A       S   P          1   G       C   4         H                   or  "Relatedness" options "The Pool" "The Pool" refers to the pool of posts and pages that are candidates for display as related to the current entry. %f is the YARPP match score between the current entry and this related entry. You are seeing this value because you are logged in to WordPress as an administrator. It is not shown to regular visitors. (Update options to reload.) Automatically display related posts? Before / after (Excerpt): Before / after (excerpt): Before / after each related entry: Before / after related entries display: Before / after related entries: Bodies:  Categories:  Click to toggle Cross-relate posts and pages? Default display if no results: Disallow by category: Disallow by tag: Display options <small>for RSS</small> Display options <small>for your website</small> Display related posts in feeds? Display related posts in the descriptions? Display using a custom template file Do you really want to reset your configuration? Donate to mitcho (Michael Yoshitaka Erlewine) for this plugin via PayPal Example post  Excerpt length (No. of words): Follow <a href="http://twitter.com/yarpp/">Yet Another Related Posts Plugin on Twitter</a> For example: Help promote Yet Another Related Posts Plugin? If, despite this check, you are sure that <code>%s</code> is using the MyISAM engine, press this magic button: Match threshold: Maximum number of related posts: NEW! No YARPP template files were found in your theme (<code>TEMPLATEPATH</code>)  so the templating feature has been turned off. No related posts. Options saved! Order results: Please move the YARPP template files into your theme to complete installation. Simply move the sample template files (currently in <code>wp-content/plugins/yet-another-related-posts-plugin/yarpp-templates/</code>) to the <code>TEMPLATEPATH</code> directory. Please try <A>manual SQL setup</a>. RSS display code example Related Posts Related Posts (YARPP) Related entries may be displayed once you save your entry Related posts brought to you by <a href='http://mitcho.com/code/yarpp/'>Yet Another Related Posts Plugin</a>. Related posts: Reset options Settings Show excerpt? Show only posts from the past NUMBER UNITS Show only previous posts? Show password protected posts? Tags:  Template file: The MyISAM check has been overridden. You may now use the "consider titles" and "consider bodies" relatedness criteria. The YARPP database had an error but has been fixed. The YARPP database has an error which could not be fixed. The higher the match threshold, the more restrictive, and you get less related posts overall. The default match threshold is 5. If you want to find an appropriate match threshhold, take a look at some post's related posts display and their scores. You can see what kinds of related posts are being picked up and with what kind of match scores, and determine an appropriate threshold for your site. There is a new beta (VERSION) of Yet Another Related Posts Plugin. You can <A>download it here</a> at your own risk. There is a new version (VERSION) of Yet Another Related Posts Plugin available! You can <A>download it here</a>. These are the related entries for this entry. Updating this post may change these related posts. This advanced option gives you full power to customize how your related posts are displayed. Templates (stored in your theme folder) are written in PHP. This option automatically displays related posts right after the content on single entry pages. If this option is off, you will need to manually insert <code>related_posts()</code> or variants (<code>related_pages()</code> and <code>related_entries()</code>) into your theme files. This option displays related posts at the end of each item in your RSS and Atom feeds. No template changes are needed. This option displays the related posts in the RSS description fields, not just the content. If your feeds are set up to only display excerpts, however, only the description field is used, so this option is required for any display at all. This option will add the code %s. Try turning it on, updating your options, and see the code in the code example to the right. These links and donations are greatly appreciated. Title: Titles:  To restore these features, please update your <code>%s</code> table by executing the following SQL directive: <code>ALTER TABLE `%s` ENGINE = MyISAM;</code> . No data will be erased by altering the table's engine, although there are performance implications. Trust me. Let me use MyISAM features. Update options Website display code example When the "Cross-relate posts and pages" option is selected, the <code>related_posts()</code>, <code>related_pages()</code>, and <code>related_entries()</code> all will give the same output, returning both related pages and posts. Whether all of these related entries are actually displayed and how they are displayed depends on your YARPP display options. YARPP is different than the <a href="http://wasabi.pbwiki.com/Related%20Entries">previous plugins it is based on</a> as it limits the related posts list by (1) a maximum number and (2) a <em>match threshold</em>. YARPP's "consider titles" and "consider bodies" relatedness criteria require your <code>%s</code> table to use the <a href='http://dev.mysql.com/doc/refman/5.0/en/storage-engines.html'>MyISAM storage engine</a>, but the table seems to be using the <code>%s</code> engine. These two options have been disabled. Yet Another Related Posts Plugin Options by <a href="http://mitcho.com/">mitcho (Michael 芳貴 Erlewine)</a> category consider consider with extra weight date (new to old) date (old to new) day(s) do not consider month(s) more&gt; require at least one %s in common require more than one %s in common score (high relevance to low) score (low relevance to high) tag title (alphabetical) title (reverse alphabetical) week(s) word Project-Id-Version: YARPP in italiano
Report-Msgid-Bugs-To: 
POT-Creation-Date: 2011-03-26 21:26+0100
PO-Revision-Date: 
Last-Translator: Gianni Diurno (aka gidibao) <gidibao[at]gmail[dot]com>
Language-Team: Gianni Diurno | http://gidibao.net/ <gidibao@gmail.com>
MIME-Version: 1.0
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit
X-Poedit-Language: Italian
X-Poedit-Country: ITALY
X-Poedit-SourceCharset: utf-8
X-Poedit-KeywordsList: _e;__
X-Poedit-Basepath: ../
: 
X-Poedit-SearchPath-0: .
  oppure Opzioni "Affinità" "Veduta di insieme" Per "Veduta di insieme" si intende il totale degli articoli e pagine che sono cadidati per essere mostrati quali correlati all'articolo in merito. %f é il punteggio di affinità YARPP tra questo articolo principale ed i suoi relativi. Stai vedendo questo messaggio perché sei collegato come amministratore di WordPress. Il messaggio lo vedi solo tu. (Ricarica la pagina per visualizzare l'aggiornamento) Desideri mostrare in automatico gli articoli correlati? Davanti / in coda (Riassunto): Davanti / in coda (riassunto): Davanti / in coda ad ogni articolo correlato: Mostra davanti / in coda agli articoli correlati: Davanti / in coda agli articoli correlati: Contenuti: Categorie: Clicca per commutare Relazione incrociata per articoli e pagine? Testo predefinito da mostrare in assenza di risultati: Escludi le categorie: Escludi i tag: Opzioni di visualizzazione nel tuo <small>feed RSS</small> Opzioni di visualizzazione nel <small>tuo sito</small> Desideri mostrare gli articoli correlati nei feed? Desideri mostrare gli articoli correlati nelle descrizioni? Mostra utilizzando un file template personalizzato Sei certo di volere ripristinare la tua configurazione? Effettua una donazione via PayPal per mitcho (Michael Yoshitaka Erlewine) lo sviluppatore di questo plugin Articolo di esempio Lunghezza riassunto (totale parole): Segui via Twitter il plugin <a href="http://twitter.com/yarpp/">Yet Another Related Posts</a> Esempio: Desideri promuovere il plugin Yet Another Related Posts? Se, nonostante questa nota, fossi certo che <code>%s</code> stia utilizzando il MyISAM engine, premi il pulsante magico: Valore di corrispondenza: Numero max. di articoli correlati: NUOVO! Poiché nessun file template YARPP é stato trovato nel tuo tema (<code>TEMPLATEPATH</code>)  la funzione template é stata disattivata. Nessun articolo correlato. Le opzioni sono state salvate! Disposizione dei risultati: ATTENZIONE: per potere completare l'installazione, metti nella cartella del tuo tema i file del template di YARPP. I file del template dimostrativo (al momento sotto <code>wp-content/plugins/yet-another-related-posts-plugin/yarpp-templates/</code>) dovranno essere allocati nella cartella <code>TEMPLATEPATH</code>. Prova con il <A>setup manuale di SQL</a>. Esempio codice RSS Articoli correlati Related Posts (YARPP) Le pubblicazioni correlate saranno mostrate una volta salvato l'articolo Articoli correlati elaborati via <a href='http://mitcho.com/code/yarpp/'>Yet Another Related Posts</a>. Articoli correlati: Ripristina le opzioni Impostazioni Desideri mostrare il riassunto? Mostra solamente gli articoli dalle precedenti NUMBER UNITS Desideri mostrare solamente gli articoli pubblicati in precedenza? Desideri mostrare gli articoli protetti da una password? Tag:  File template: La verifica MyISAM é stata sovrascritta. Potrai da ora utilizzare "considera titoli" e  "considera contenuti" come criteri di affinità. Il database di YARPP aveva un errore, ma é stato corretto. Il database di YARPP ha un errore che non può essere corretto. Quanto più alto sarà il valore di corrispondenza, maggiore sarà la restrizione: otterrai di fatto un minore numero di articoli correlati. Il valore predefinito é impostato a 5. Qualora desiderassi trovare un valore appropriato per determinare le affinità, verifica gli articoli correlati di alcuni post ed il punteggio a loro associato. Potrai quindi determinare quale sia il migliore valore di corrispondenza per il tuo sito. E' disponibile una nuova (VERSIONE) beta di Yet Another Related Posts Plugin. Puoi <A>scaricarla qui</a> a tuo rischio e pericolo. E' disponibile una nuova versione (VERSIONE) di Yet Another Related Posts Plugin! Puoi <A>scaricarla qui</a>. Questi sono gli articoli correlati per questo post. L'aggiornamento di questo post potrebbe cambiare gli articoli ad esso correlati. Le opzioni avanzate ti permettono una completa personalizzazione per la visualizzazione degli articoli correlati. I template (allocati nella cartella del tuo tema) sono stati scritti in PHP. Questa opzione farà in modo che gli articoli correlati vengano mostrati automaticamente in coda al contenuto di ogni singola pubblicazione . Qualora questa opzione non fosse stata attivata, dovrai inserire manualmente <code>related_posts()</code> oppure le varianti (<code>related_pages()</code> e <code>related_entries()</code>) nei file del tuo tema. Questa opzione mostra gli articoli correlati in coda ad ogni articolo nei tuoi feed RSS e Atom. Non é necessaria alcuna modifica al template. Questa opzione mostrerà gli articoli correlati nei campi della descrizione del feed RSS e non solo nei contenuti. Se i tuoi feed fossero stati impostati per mostrare solamente i riassunti degli articoli, in ogni caso verrà utilizzato il campo per la descrizione quindi, questa opzione é comunque necessaria. Questa opzione aggiugerà il codice %s. Attivalo, aggiorna le opzioni e vedi l'anteprima del codice qui a lato. Ti sarei molto grato se tu mostrassi il mio link. Titolo: Titoli: Per poter ripristinare queste funzioni dovrai aggiornare la tua tabella <code>%s</code> facendo sì che sia eseguita la seguente direttiva SQL: <code>ALTER TABLE `%s` ENGINE = MyISAM;</code> . Nessun dato verrà perso modificando la tabella del motore sebbene ne verranno interessate le prestazioni. Abbi fiducia. Lasciami utilizzare le funzioni MyISAM. Aggiorna le opzioni Esempio codice Una volta selezionata l'opzione "Relazione incrociata articoli e pagine", <code>related_posts()</code>, <code>related_pages()</code> e <code>related_entries()</code> forniranno tutti lo stesso output verso le pagine e gli articoli correlati. La visualizzazione degli articoli correlati dipende principalmente dalle opzioni YARPP. YARPP é differente rispetto ai <a href="http://wasabi.pbwiki.com/Related%20Entries">precedenti plugin</a> in quanto esso limita la lista degli articoli correlati (1) ad un numero massimo e (2) ad un <em>valore di corrispondenza</em>. I criteri di affinità YARPP "considera titoli" e "considera contenuti" necessitano che la tua tabella <code>%s</code> possa utilizzare il <a href='http://dev.mysql.com/doc/refman/5.0/en/storage-engines.html'>MyISAM storage engine</a>. Pare che sia in uso il <code>%s</code> engine. Queste due opzioni sono state disattivate. Opzioni Yet Another Related Posts di <a href="http://mitcho.com/">mitcho (Michael 芳貴 Erlewine)</a> categoria considera considera con maggior rilevanza data (dal nuovo al vecchio) data (dal vecchio al nuovo) giorno(i) non considerare mese(i) info&gt; richiedi almeno 1 %s in comune richiedi più di 1 %s in comune punteggio (da massima a minima rilevanza) punteggio (da minima a massima rilevanza) tag titolo (A-Z) titolo (Z-A) settimana(e) parola 