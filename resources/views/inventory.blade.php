@extends('layouts.app')

@push('styles')
.badge-success {
  background-color: #2196f3;
}
.badge-warning {
  background-color: #ff9800;
}
.table {
  border: 1px solid #ccc;
} 
.table thead {
    --bs-table-bg: #2196f3;
    --bs-table-color: #fff;
}
.table>:not(caption)>*>* {
  padding: .5rem .5rem;
}
@endpush

@section('content')

<div class="container">
  <div class="row">
    <div class="col-12">
      <div class="portfolio-details mt-5 mb-5">
        <div class="portfolio-info aos-init aos-animate" data-aos="fade-up" data-aos-delay="200">
          <h3>Store Issuance</h3>
          <ul>
            {{-- <li><strong>Employee Code: </strong>{{$inventory->emp_code}}</li> --}}
            {{-- <li><strong>Employee Name: </strong>{{$inventory->emp_name}}</li> --}}
          </ul>
          <table class="table mt-5 mb-5">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Item Code</th>
                    <th>Item Description</th>
                    <th>Item Quantity</th>
                    <th>Rate</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
              @if ($inventory->isEmpty())
                <tr>
                    <td colspan="6" class="text-center">No records found.</td>
                </tr>
              @else
                @foreach ($inventory as $inv)
                  <tr>
                      <td>{{ date('d-m-Y',strtotime($inv->doc_date)) }}</td>
                      <td>{{ $inv->item_code }}</td>
                      <td>{{ $inv->inventory->item_desc }}</td>
                      <td>{{ $inv->qty }}</td>
                      <td>{{ $inv->rate }}</td>
                      <td>{{ $inv->value }}</td>
                  </tr>
                @endforeach
              @endif
                
            </tbody>
        </table>
        </div>
      </div>

    </div>
  </div>
</div>

@endsection

@push('scripts')

@endpush




