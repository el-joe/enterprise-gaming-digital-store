@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
<img src="{{ asset(config('brand.email_logo.path')) }}" width="{{ config('brand.email_logo.width') }}" height="{{ config('brand.email_logo.height') }}" alt="{{ config('brand.name') }}" style="display: block; border: 0; height: {{ config('brand.email_logo.height') }}px; width: {{ config('brand.email_logo.width') }}px; max-width: 100%;">
</a>
</td>
</tr>
