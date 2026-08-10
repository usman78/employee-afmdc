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
.nav-tabs .nav-link {
  color: #333;
  border: 1px solid #dee2e6;
}
.nav-tabs .nav-link.active {
  background-color: #2196f3;
  color: #fff;
  border-color: #2196f3;
}
.acknowledged-badge {
  background-color: #28a745;
  color: #fff;
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
}
.portfolio-details .portfolio-info ul li {
    margin-top: 10px;
}
@media (min-width: 1200px) {
    .container, .container-sm, .container-md, .container-lg, .container-xl {
        max-width: 90%;
    }
}
@endpush

@section('content')
<div class="container">
  <div class="row">
    <div class="col-12">
      <div class="portfolio-details mt-5 mb-5">
        <div class="portfolio-info">
          <h3>Store Issuance</h3>
          <!-- Tabs -->
          <ul class="nav nav-tabs mt-4 mb-4" role="tablist">
            <li class="nav-item">
              <a class="nav-link active" id="routine-tab" data-bs-toggle="tab" href="#routine" role="tab" data-tab="routine">Routine Issues</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" id="issues-tab" data-bs-toggle="tab" href="#issues" role="tab" data-tab="issues">Named Issues</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" id="acknowledged-tab" data-bs-toggle="tab" href="#acknowledged" role="tab" data-tab="acknowledged">Acknowledged Issues</a>
            </li>
          </ul>

          <!-- Tab Content -->
          <div class="tab-content">
            <!-- Routine Issues Tab -->
            <div class="tab-pane fade show active" id="routine" role="tabpanel">
              <table class="table mt-3 mb-5">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Issue</th>
                        <th>Item Code</th>
                        <th>Item Description</th>
                        <th>Quantity</th>
                        <th>Rate</th>
                        <th>Value</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                  @if ($routineIssues->isEmpty())
                    <tr>
                        <td colspan="9" class="text-center">No routine issues found.</td>
                    </tr>
                  @else
                    @foreach ($routineIssues as $inv)
                        <tr>
                            <td>{{ date('d-m-Y',strtotime($inv->doc_date)) }}</td>
                            <td>{{ $inv->doc_no }}</td>
                            <td>{{ $inv->item_code }}</td>
                            <td>{{ $inv->inventory ? $inv->inventory->item_desc : '-' }}</td>
                            <td>{{ $inv->qty }}</td>
                            <td>{{ $inv->rate }}</td>
                            <td>{{ $inv->value }}</td>
                            @if ($inv->ackn_by_user != 'Y')
                            <td>
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#acknowledgeModal" 
                                  onclick="setItemId({{ json_encode($inv->item_code) }}, {{ json_encode($inv->doc_no) }})">
                                  Acknowledge
                                </button>
                            </td>
                            @else
                            <td><span class="text-success fw-bold"><span class="fas fa-fw fa-check"></span> Acknowledged</span></td>
                            @endif
                        </tr>
                    @endforeach
                  @endif
                </tbody>
              </table>
              <!-- Routine Issues Pagination -->
              <div class="d-flex justify-content-center">
                {{ $routineIssues->appends(request()->query())->appends(['tab' => 'routine'])->links('pagination::bootstrap-4', ['pageName' => 'routine_page']) }}
              </div>
            </div>

            <!-- Named Issues Tab -->
            <div class="tab-pane fade" id="issues" role="tabpanel">
              <table class="table mt-3 mb-5">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Issue</th>
                        <th>Item Code</th>
                        <th>Item Description</th>
                        <th>Quantity</th>
                        <th>Rate</th>
                        <th>Value</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                  @if ($unacknowledged->isEmpty())
                    <tr>
                        <td colspan="7" class="text-center">No pending issues found.</td>
                    </tr>
                  @else
                    @foreach ($unacknowledged as $inv)
                      <tr>
                          <td>{{ date('d-m-Y',strtotime($inv->doc_date)) }}</td>
                          <td>{{ $inv->doc_no }}</td>
                          <td>{{ $inv->item_code }}</td>
                          <td>{{ $inv->inventory->item_desc }}</td>
                          <td>{{ $inv->qty }}</td>
                          <td>{{ $inv->rate }}</td>
                          <td>{{ $inv->value }}</td>
                          @if($inv->ackn_by_user == 'Y')
                            <td><span class="text-success fw-bold"><span class="fas fa-fw fa-check"></span> Acknowledged</span></td>
                          @else
                          <td>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#acknowledgeModal" 
                              onclick="setItemId({{ json_encode($inv->item_code) }}, {{ json_encode($inv->doc_no) }})">
                              Acknowledge
                            </button>
                          </td>
                          @endif
                      </tr>
                    @endforeach
                  @endif
                </tbody>
              </table>
              <!-- Named Issues Pagination -->
              <div class="d-flex justify-content-center">
                {{ $unacknowledged->appends(request()->query())->appends(['tab' => 'issues'])->links('pagination::bootstrap-4', ['pageName' => 'unack_page']) }}
              </div>
            </div>

            <!-- Acknowledged Issues Tab -->
            <div class="tab-pane fade" id="acknowledged" role="tabpanel">
              <table class="table mt-3 mb-5">
                <thead>
                    <tr>
                        <th>Date Issued</th>
                        <th>Issue</th>
                        <th>Item Code</th>
                        <th>Item Description</th>
                        <th>Quantity</th>
                        <th>Rate</th>
                        <th>Value</th>
                        <th>Acknowledged Date</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                  @if ($allAcknowledged->isEmpty())
                    <tr>
                        <td colspan="8" class="text-center">No acknowledged issues found in the current year.</td>
                    </tr>
                  @else
                    @foreach ($allAcknowledged as $inv)
                      <tr>
                          <td>{{ date('d-m-Y',strtotime($inv->doc_date)) }}</td>
                          <td>{{ $inv->doc_no }}</td>
                          <td>{{ $inv->item_code }}</td>
                          <td>{{ $inv->inventory->item_desc }}</td>
                          <td>{{ $inv->qty }}</td>
                          <td>{{ $inv->rate }}</td>
                          <td>{{ $inv->value }}</td>
                          <td>
                            <span class="acknowledged-badge">
                              {{ date('d-m-Y H:i',strtotime($inv->dated)) }}
                            </span>
                          </td>
                          <td>{{ $inv->remarks ?? '-' }}</td>
                      </tr>
                    @endforeach
                  @endif
                </tbody>
              </table>
              <!-- Acknowledged Issues Pagination -->
              <div class="d-flex justify-content-center">
                {{ $allAcknowledged->appends(request()->query())->appends(['tab' => 'acknowledged'])->links('pagination::bootstrap-4', ['pageName' => 'ack_page']) }}
              </div>
            </div>
          </div>

        </div>
      </div>

    </div>
  </div>
</div>

<!-- Acknowledge Modal -->
<div class="modal fade" id="acknowledgeModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Acknowledge Item Receipt</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="acknowledgeForm">
        @csrf
        <div class="modal-body">
          <div class="form-group mb-3">
            <label for="remarks">Remarks (Optional)</label>
            <textarea class="form-control" id="remarks" name="remarks" rows="3" placeholder="Enter any remarks about the item receipt"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Acknowledge</button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@push('scripts')

let issue_item_code = null;
let issue_doc_no = null;

function setItemId(itemId, docNo) {
    issue_item_code = itemId;
    issue_doc_no = docNo;
    document.getElementById('acknowledgeForm').reset();
}

document.getElementById('acknowledgeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const remarks = document.getElementById('remarks').value;
    
    // Get current tab before reload
    const urlParams = new URLSearchParams(window.location.search);
    const currentTab = urlParams.get('tab') || 'routine';
    
    // Make AJAX request to acknowledge the item
    fetch(`/inventory/acknowledge/${encodeURIComponent(issue_item_code)}/${encodeURIComponent(issue_doc_no)}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            remarks: remarks
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            // Reload the page and preserve tab state
            const reloadUrl = new URL(window.location);
            reloadUrl.searchParams.set('tab', currentTab);
            window.location.href = reloadUrl.toString();
        } else {
            alert('Error: ' + (data.error || 'Failed to acknowledge item'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while acknowledging the item');
    });
    
    // Close the modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('acknowledgeModal'));
    modal.hide();
});

// Handle tab state preservation
document.addEventListener('DOMContentLoaded', function() {
    // Get tab parameter from URL
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab') || 'routine'; // Default to 'routine'
    
    // Activate the tab based on URL parameter
    const tabElement = document.querySelector(`[data-tab="${activeTab}"]`);
    if (tabElement) {
        const tabInstance = new bootstrap.Tab(tabElement);
        tabInstance.show();
    }
    
    // Update URL when tab is clicked
    document.querySelectorAll('[data-tab]').forEach(tab => {
        tab.addEventListener('click', function() {
            const tabName = this.getAttribute('data-tab');
            const currentUrl = new URL(window.location);
            currentUrl.searchParams.set('tab', tabName);
            window.history.pushState({}, '', currentUrl);
        });
    });
});

@endpush




