@extends('layouts.app')
@section('content')
    <section id="services" class="services section">
      <!-- Section Title -->
      <div class="container section-title">
        {{ dd($sops) }}
        <h2>SOP's</h2>
        <p>View the SOPs defined for your role, your department and all other departments.</p>
      </div><!-- End Section Title -->
      <div class="container">
        <div class="accordion" id="accordionExample">
          @foreach ($sops as $sop_group)
            @if ($sop_group == null)
              @continue
            @else
              <div class="accordion-item bg-blue-500 text-green p-2">
                  <h2 class="accordion-header" id="heading{{ $loop->index }}">
                      <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" 
                              type="button" 
                              data-bs-toggle="collapse" 
                              data-bs-target="#collapse{{ $loop->index }}" 
                              aria-expanded="{{ $loop->first ? 'true' : 'false' }}" 
                              aria-controls="collapse{{ $loop->index }}">
                              DEPARTMENT OF &nbsp;<strong>{{  $sop_group['department'] }}</strong>&nbsp; SOPs
                      </button>
                  </h2>

                  <div id="collapse{{ $loop->index }}" 
                      class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" 
                      aria-labelledby="heading{{ $loop->index }}" 
                      data-bs-parent="#accordionExample">
                      <div class="accordion-body">
                        @if ($sop_group['sops']->isEmpty())
                          <p><em>No SOPs available for this department.</em></p>
                        @else
                          @foreach ($sop_group['sops'] as $sop)
                            @php
                              $pdfPath = asset("storage/sop_documents/{$sop->department_id}/{$sop->document_path}");
                            @endphp
                            <li><a href="{{$pdfPath}}">{{ $sop->title }}</a></li>
                          @endforeach
                        @endif
                      </div>
                  </div>
              </div>
            @endif
          @endforeach  
        </div>
      </div>
    </section>
@endsection      