@foreach($rows as $row)
<tr>
    <td>{{ $row['libelle'] }}</td>
    <td>{{ $row['distance'] }}</td>
    <td>{{ $row['vitesse_max'] }}</td>
    <td>{{ $row['station'] }}</td>
</tr>
@endforeach
