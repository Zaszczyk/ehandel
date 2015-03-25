{{ content() }}

<h2>Produkty</h2>

{% for product in page.items %}
{% if loop.first %}
<table class="table table-bordered table-striped" align="center">
    <thead>
    <tr>
        <th>Id</th>
        <th>Zdjęcie</th>
        <th>Nazwa</th>
        <th>Opis</th>
        <th>Cena</th>
        <th>Zamów</th>
    </tr>
    </thead>
    {% endif %}
    <tbody>
    <tr>
        <td>{{ product.id }}</td>
        <td>{{ product.image }}</td>
        <td>{{ product.name }}</td>
        <td>{{ product.description }}</td>
        <td>{{ product.price }}</td>
        <td>{{ link_to("order/create/" ~ product.id, '<i class="glyphicon glyphicon-edit"></i> Zamów', "class": "btn btn-default") }}</td>
    </tr>
    </tbody>
    {% if loop.last %}
    <tbody>
    <tr>
        <td colspan="7" align="right">
            <div class="btn-group">
                {{ link_to("products/index", '<i class="icon-fast-backward"></i> Pierwsza', "class": "btn btn-default") }}
                {{ link_to("products/index?page=" ~ page.before, '<i class="icon-step-backward"></i> Poprzednia', "class": "btn btn-default") }}

                <span class="btn btn-default">{{ page.current }}/{{ page.total_pages }}</span>
                {{ link_to("products/index?page=" ~ page.next, '<i class="icon-step-forward"></i> Następna', "class": "btn btn-default") }}
                {{ link_to("products/index?page=" ~ page.last, '<i class="icon-fast-forward"></i> Ostatnia', "class": "btn btn-default") }}

            </div>
        </td>
    </tr>
    <tbody>
</table>
{% endif %}
{% endfor %}

</fieldset>

</form>
