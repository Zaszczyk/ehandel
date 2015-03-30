{{ content() }}


<div class="jumbotron">
    <h2>Zamówienie opłacone</h2>
    <p>Przedmiot {{ post['item_name']  }} został opłacony kwotą {{ post['mc_gross'] }} {{ post['mc_currency'] }}.<br />
        Unikalny kod transakcji to: {{ post['txn_id'] }}</p>
</div>

