
{{ content() }}



{{ form("orders/pay") }}

<h2>Opłać zamówienie</h2>

<fieldset>

{% for element in form %}
    {% if is_a(element, 'Phalcon\Forms\Element\Hidden') %}
{{ element }}
    {% else %}
<div class="control-group">
    {{ element.label(['class': 'control-label']) }}
    <div class="controls">
        {{ element }}
    </div>
</div>
    {% endif %}
{% endfor %}

<div class="control-group">
    {{ submit_button("Przejdź do PayPal", "class": "btn btn-primary") }}
</div>

</fieldset>

</form>
