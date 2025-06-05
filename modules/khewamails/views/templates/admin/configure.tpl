<h2>Collected Emails</h2>
<ul>
    {foreach from=$emails item=email}
        <li>{$email.email} - {$email.date_add}</li>
    {/foreach}
</ul>
