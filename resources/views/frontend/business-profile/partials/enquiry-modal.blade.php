{{--
  The profile's "Send enquiry" popup (for logged-in visitors). A consultant or service provider is
  asked about one of its services; a vendor gets the visitor's contact details and requirement.
  $biz, $enquiry: ['action' => url, 'serviceField' => ?string, 'services' => ?Collection]
--}}
@php
    $services = collect($enquiry['services'] ?? []);
    $isVendor = $biz['type'] === 'vendor';
@endphp

<div class="modal fade biz-enquiry-modal" id="bizEnquiryModal" tabindex="-1" aria-labelledby="bizEnquiryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered {{ $isVendor ? '' : 'modal-lg' }}">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <p class="consultant-service-detail-modal__eyebrow mb-1">Send an enquiry</p>
                    <h3 class="modal-title" id="bizEnquiryModalLabel">{{ $biz['name'] }}</h3>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            @if($isVendor)
                <form action="{{ $enquiry['action'] }}" method="POST" data-biz-ajax>
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label" for="bizEnquiryEmail">Email *</label>
                                <input type="email" id="bizEnquiryEmail" name="email" value="{{ auth()->user()?->email }}" class="form-control" required>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label" for="bizEnquiryPhone">Number *</label>
                                <input type="text" id="bizEnquiryPhone" name="phone_number" value="{{ auth()->user()?->phone_number }}" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="bizEnquiryContact">Way to connect *</label>
                                <select id="bizEnquiryContact" name="preferred_contact" class="form-select" required>
                                    <option value="text">Text</option>
                                    <option value="whatsapp">WhatsApp</option>
                                    <option value="call">Call</option>
                                    <option value="email">Email</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="bizEnquiryReason">Your requirement *</label>
                                <textarea id="bizEnquiryReason" name="reason" class="form-control" rows="4" placeholder="Tell the vendor what you need" required></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn consultant-service-detail-modal__contact-btn">Send enquiry</button>
                    </div>
                </form>
            @elseif($services->isEmpty())
                <div class="modal-body">
                    <p class="mb-0 text-muted">Enquiries open once this {{ strtolower($biz['typeLabel']) }} lists a service. Meanwhile you can call or message them using the contact details on this page.</p>
                </div>
            @else
                <form action="{{ $enquiry['action'] }}" method="POST" enctype="multipart/form-data" data-biz-ajax>
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="bizEnquiryService">Service *</label>
                            <select id="bizEnquiryService" name="{{ $enquiry['serviceField'] }}" class="form-select" required>
                                <option value="">Choose a service</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}">{{ $service->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @include('frontend.business-profile.partials.service-enquiry-fields', ['questionPlaceholder' => 'Write your question'])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn consultant-service-detail-modal__contact-btn">Submit enquiry</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
