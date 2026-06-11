@extends('frontend.layouts.app')

@section('meta_title', 'Refund Policy | SoilnWater')
@section('meta_description', 'Review the SoilnWater placeholder refund policy for subscriptions, advertisements, featured listings, and marketplace services.')

@section('content')
<div class="about-page refund-policy-page">
  <section class="about-banner refund-policy-banner">
    <h1>Refund Policy</h1>
    <p>This static placeholder page explains how refund requests may be reviewed for SoilnWater services. Final policy details will be updated soon.</p>
  </section>

  <div class="about-inner refund-policy-inner">
    <section class="sec about-intro">
      <div class="sec-head">
        <div class="sec-title"><span class="icon"><i class="fa-solid fa-rotate-left"></i></span> Policy Overview</div>
      </div>
      <p>SoilnWater is currently using this dummy refund policy content for display and review purposes only. The policy applies to paid promotions, advertising placements, featured listings, subscriptions, and other digital services purchased through the platform.</p>
      <p>All refund requests are reviewed case-by-case by our support team. Approval may depend on service usage, campaign status, billing records, and the reason shared by the customer.</p>
    </section>

    <section class="sec">
      <div class="sec-head">
        <div class="sec-title"><span class="icon"><i class="fa-solid fa-circle-check"></i></span> Eligible Refund Scenarios</div>
      </div>
      <ul class="about-list">
        <li>Duplicate payment or accidental double charge for the same order.</li>
        <li>Payment deducted but the selected service was not activated.</li>
        <li>Technical issue from SoilnWater that prevented service delivery.</li>
        <li>Cancellation request submitted before a campaign, listing, or subscription becomes active.</li>
        <li>Billing mismatch confirmed by the SoilnWater accounts or support team.</li>
      </ul>
    </section>

    <section class="sec">
      <div class="sec-head">
        <div class="sec-title"><span class="icon"><i class="fa-solid fa-ban"></i></span> Non-Refundable Items</div>
      </div>
      <div class="about-grid refund-policy-grid">
        <div class="about-box">
          <h4>Completed Services</h4>
          <p>Charges for completed advertisements, expired campaigns, or delivered promotional services are generally non-refundable.</p>
        </div>
        <div class="about-box">
          <h4>Active Campaigns</h4>
          <p>Refunds may not be available once an ad campaign, featured listing, or promotional package has already gone live.</p>
        </div>
        <div class="about-box">
          <h4>User-Submitted Errors</h4>
          <p>Orders placed with incorrect information, category selection, location, media, or pricing details may not qualify for a refund.</p>
        </div>
        <div class="about-box">
          <h4>Third-Party Costs</h4>
          <p>Payment gateway fees, taxes, third-party service charges, and processing fees may be deducted where applicable.</p>
        </div>
      </div>
    </section>

    <section class="sec">
      <div class="sec-head">
        <div class="sec-title"><span class="icon"><i class="fa-solid fa-list-check"></i></span> Refund Request Process</div>
      </div>
      <div class="about-approach refund-policy-steps">
        <div class="about-approach-item">
          <h5>1. Submit Request</h5>
          <p>Contact support with your order ID, payment proof, registered contact details, and reason for refund.</p>
        </div>
        <div class="about-approach-item">
          <h5>2. Review</h5>
          <p>Our team checks payment status, service activation, and usage history before making a decision.</p>
        </div>
        <div class="about-approach-item">
          <h5>3. Confirmation</h5>
          <p>Approved or rejected refund updates will be shared through email, phone, or the platform notification channel.</p>
        </div>
        <div class="about-approach-item">
          <h5>4. Processing</h5>
          <p>Approved refunds may take 7-10 business days to reflect in the original payment method.</p>
        </div>
      </div>
    </section>

    <section class="about-mission-why refund-policy-notes">
      <div class="sec">
        <div class="sec-head">
          <div class="sec-title"><span class="icon"><i class="fa-solid fa-clock"></i></span> Review Timeline</div>
        </div>
        <p>Refund requests should be raised within 7 calendar days from the payment date. Requests submitted after this window may be declined unless a verified service-delivery issue occurred.</p>
      </div>

      <div class="sec">
        <div class="sec-head">
          <div class="sec-title"><span class="icon"><i class="fa-solid fa-headset"></i></span> Contact Support</div>
        </div>
        <p>For refund assistance, please contact the SoilnWater support team with full payment and account details. Dummy support email: <strong>support@soilnwater.example</strong>.</p>
      </div>
    </section>

    <section class="about-cta">
      <h2>Important Note</h2>
      <p>This is dummy static content and does not represent the final legal refund policy of SoilnWater.</p>
    </section>
  </div>
</div>
@endsection
