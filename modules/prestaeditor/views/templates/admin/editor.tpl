{if $pe_message}
  <div class="alert alert-success">{$pe_message|escape:'html':'UTF-8'}</div>
{/if}

<div class="pe-editor">
  <aside class="pe-sidebar">
    <div class="pe-head">
      <strong>Presta File Editor</strong>
      <span></span>
      <code>/{$pe_dir|escape:'html':'UTF-8'}</code>
    </div>

    <form class="pe-search" method="get">
      <input type="hidden" name="controller" value="AdminPrestaEditor">
      <input type="hidden" name="token" value="{$pe_token|escape:'html':'UTF-8'}">
      <input class="pe-input" name="q" value="{$pe_query|escape:'html':'UTF-8'}" placeholder="Search content or filename">
      <button class="pe-button" type="submit">Search</button>
    </form>

    {if $pe_query}
      <div class="pe-muted">
        Found {$pe_search.results|count} · mode {$pe_search.mode|escape:'html':'UTF-8'} · PHP checked {$pe_search.checked|intval}
      </div>
      <div class="pe-results">
        {foreach $pe_search.results as $result}
          <a class="pe-item" href="{$pe_current_index|escape:'html':'UTF-8'}&token={$pe_token|escape:'url'}&dir={$result.dir|escape:'url'}&file={$result.file|escape:'url'}&q={$pe_query|escape:'url'}">{$result.rel|escape:'html':'UTF-8'}</a>
        {/foreach}
      </div>
    {/if}

    <form class="pe-create" method="post">
      <input type="hidden" name="token" value="{$pe_token|escape:'html':'UTF-8'}">
      <input type="hidden" name="dir" value="{$pe_dir|escape:'html':'UTF-8'}">
      <input class="pe-input" name="file" placeholder="new-name.php">
      <button class="pe-button" name="pe_action" value="newfile">File</button>
      <button class="pe-button" name="pe_action" value="newdir">Dir</button>
    </form>

    <nav class="pe-list">
      {if $pe_dir}
        <a class="pe-item" href="{$pe_current_index|escape:'html':'UTF-8'}&token={$pe_token|escape:'url'}&dir={$pe_parent|escape:'url'}&q={$pe_query|escape:'url'}">..</a>
      {/if}

      {foreach $pe_dirs as $dir}
        <a class="pe-item pe-folder" href="{$pe_current_index|escape:'html':'UTF-8'}&token={$pe_token|escape:'url'}&dir={$dir.rel|escape:'url'}&q={$pe_query|escape:'url'}">[{$dir.name|escape:'html':'UTF-8'}]</a>
      {/foreach}

      {foreach $pe_files as $file}
        <a class="pe-item {if $file.name == $pe_file}is-active{/if}" href="{$pe_current_index|escape:'html':'UTF-8'}&token={$pe_token|escape:'url'}&dir={$pe_dir|escape:'url'}&file={$file.name|escape:'url'}&q={$pe_query|escape:'url'}">{$file.name|escape:'html':'UTF-8'}</a>
      {/foreach}
    </nav>
  </aside>

  <main class="pe-main">
    <form class="pe-toolbar" method="post" id="feForm" data-pe-chunk-size="{$pe_chunk_size|intval}">
      <input type="hidden" name="token" value="{$pe_token|escape:'html':'UTF-8'}">
      <input type="hidden" name="dir" value="{$pe_dir|escape:'html':'UTF-8'}">
      <input type="hidden" name="file" value="{$pe_file|escape:'html':'UTF-8'}">
      <input type="hidden" name="enc" value="{$pe_encoding|escape:'html':'UTF-8'}">
      <input type="hidden" id="fePayload" name="content_cipher" value="">
      <div class="pe-path">
        {if $pe_file}{$pe_file_path|escape:'html':'UTF-8'} · {$pe_encoding|escape:'html':'UTF-8'}{else}Select a file{/if}
      </div>
      {if $pe_file && !$pe_is_binary}
        <button class="pe-button" name="pe_action" value="save">Save encrypted</button>
        <button class="pe-button pe-danger" name="pe_action" value="delete" onclick="return confirm('Delete this file?')">Delete</button>
      {/if}
    </form>

    {if $pe_is_binary}
      <div class="pe-empty">Binary file editing is blocked.</div>
    {elseif $pe_file}
      <div class="pe-cipher-chunks" hidden>
        {foreach $pe_cipher_chunks as $chunk}
          <textarea class="pe-cipher-chunk">{$chunk|escape:'html':'UTF-8'}</textarea>
        {/foreach}
      </div>
      <textarea id="feCipher" hidden>{foreach $pe_cipher_chunks as $chunk}{$chunk|escape:'html':'UTF-8'}{/foreach}</textarea>
      <textarea class="pe-textarea" id="feEditor" form="feForm" spellcheck="false" disabled>Paste the console unlock script to edit this file.</textarea>
      <div class="pe-console-state" id="pe-console-state">Locked · {$pe_cipher_chunks|count} chunk(s)</div>
    {else}
      <div class="pe-empty">Choose a file, folder, or search result.</div>
    {/if}
  </main>
</div>
