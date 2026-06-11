@extends('frontend.layouts.app')

@section('meta_title', 'Refund Policy | SoilnWater')
@section('meta_description', 'Review the SoilnWater refund policy for subscriptions, advertisements, featured listings, and marketplace services.')

@section('content')
<div class="refund-policy-page">
  <section class="refund-hero">
    <div class="refund-hero__content">
      <span class="refund-eyebrow"><i class="fa-solid fa-rotate-left"></i> Refund &amp; Cancellation Policy</span>
      <h1>SOILNWATER REFUND &amp; CANCELLATION POLICY</h1>
      <p>
        Clear guidance for cancellations, refunds, memberships, advertisements,
        promotional services, and other paid SoilnWater services.
      </p>
      <div class="refund-meta-grid" aria-label="Policy dates">
        <div class="refund-meta-card">
          <span>Effective Date</span>
          <strong>18 April, 2026</strong>
        </div>
        <div class="refund-meta-card">
          <span>Last Updated</span>
          <strong>18 April, 2026</strong>
        </div>
      </div>
    </div>
  </section>

  <main class="refund-policy-wrap">
    <aside class="refund-policy-sidebar" aria-label="Policy overview">
      <div class="refund-sidebar-card">
        <h2>Policy at a glance</h2>
        <p>Digital service purchases are generally final and non-refundable unless this policy states otherwise.</p>
        <ul>
          <li><i class="fa-solid fa-circle-check"></i> Duplicate payments may be reviewed.</li>
          <li><i class="fa-solid fa-circle-check"></i> Failed activations may be resolved or refunded.</li>
          <li><i class="fa-solid fa-circle-check"></i> Refund requests require transaction details.</li>
        </ul>
      </div>

      <nav class="refund-sidebar-card refund-policy-nav" aria-label="Refund policy sections">
        <h2>Sections</h2>
        <a href="#general-policy">General Policy</a>
        <a href="#premium-memberships">Premium Memberships</a>
        <a href="#advertisements">Advertisements</a>
        <a href="#refund-process">Refund Request Process</a>
        <a href="#contact-information">Contact Information</a>
      </nav>
    </aside>

    <div class="refund-policy-content">
      <section class="refund-intro-card">
        <p>
          This Refund &amp; Cancellation Policy ("Policy") governs refunds, cancellations,
          membership subscriptions, advertisements, promotional services, and other paid
          services offered through SoilnWater ("Platform"), owned and operated by
          Annuvedant Electronics (OPC) Private Limited ("Company", "SoilnWater", "we",
          "our", or "us").
        </p>
        <p>
          By purchasing any paid service on the Platform, you acknowledge and agree to
          this Policy.
        </p>
      </section>

      <section class="refund-policy-section" id="general-policy">
        <div class="refund-section-heading">
          <span>01</span>
          <h2>GENERAL POLICY</h2>
        </div>
        <p>SoilnWater provides digital services including, but not limited to:</p>
        <ul class="refund-check-list two-column">
          <li>Premium Memberships</li>
          <li>Featured Listings</li>
          <li>Vendor Profile Upgrades</li>
          <li>Consultant Profile Upgrades</li>
          <li>Service Provider Profile Upgrades</li>
          <li>Advertisements</li>
          <li>Sponsored Listings</li>
          <li>Promotional Campaigns</li>
          <li>Lead Generation Services</li>
          <li>Business Visibility Packages</li>
          <li>Other Digital Marketing Services</li>
        </ul>
        <p>
          Due to the nature of digital services, all purchases are generally considered
          final and non-refundable unless expressly stated otherwise in this Policy.
        </p>
      </section>

      <section class="refund-policy-section" id="premium-memberships">
        <div class="refund-section-heading">
          <span>02</span>
          <h2>PREMIUM MEMBERSHIPS</h2>
        </div>
        <h3>2.1 Membership Cancellation</h3>
        <p>Members may cancel renewal of Premium Membership at any time.</p>
        <p>
          Cancellation shall prevent future renewal charges but shall not terminate the
          active membership period already paid for.
        </p>
        <p>Membership benefits shall remain available until the expiry date.</p>

        <h3>2.2 Membership Refunds</h3>
        <p>Premium Membership fees are generally non-refundable.</p>
        <p>Refunds shall not be provided for:</p>
        <ul class="refund-check-list two-column">
          <li>Change of mind;</li>
          <li>Lack of business enquiries;</li>
          <li>Lack of customer leads;</li>
          <li>Unsatisfactory sales performance;</li>
          <li>Failure to use membership features;</li>
          <li>Business closure;</li>
          <li>Dissatisfaction with business results;</li>
          <li>Membership expiry.</li>
        </ul>
      </section>

      <section class="refund-policy-section" id="advertisements">
        <div class="refund-section-heading">
          <span>03</span>
          <h2>ADVERTISEMENTS AND FEATURED LISTINGS</h2>
        </div>
        <h3>3.1 Advertisement Orders</h3>
        <p>
          Once an advertisement, featured listing, sponsored profile, or promotional
          campaign has been approved, scheduled, published, or activated, the associated
          fees shall be non-refundable.
        </p>

        <h3>3.2 Cancellation Before Activation</h3>
        <p>
          If an advertisement has not yet been approved, scheduled, or published,
          SoilnWater may, at its sole discretion, approve a cancellation request.
        </p>
        <p>Any approved refund may be subject to administrative and processing charges.</p>

        <h3>3.3 Advertisement Performance</h3>
        <p>Refunds shall not be granted due to:</p>
        <ul class="refund-check-list two-column">
          <li>Low impressions;</li>
          <li>Low clicks;</li>
          <li>Low engagement;</li>
          <li>Low lead generation;</li>
          <li>Low conversion rates;</li>
          <li>Marketing performance dissatisfaction.</li>
        </ul>
        <p>SoilnWater does not guarantee advertising performance or business outcomes.</p>
      </section>

      <section class="refund-policy-section">
        <div class="refund-section-heading">
          <span>04</span>
          <h2>LEAD GENERATION SERVICES</h2>
        </div>
        <p>Lead generation services, featured exposure, and promotional packages do not guarantee:</p>
        <ul class="refund-check-list two-column">
          <li>Customer enquiries;</li>
          <li>Sales;</li>
          <li>Projects;</li>
          <li>Business opportunities;</li>
          <li>Revenue.</li>
        </ul>
        <p>No refund shall be provided solely because expected business results were not achieved.</p>
      </section>

      <section class="refund-policy-section">
        <div class="refund-section-heading">
          <span>05</span>
          <h2>DUPLICATE PAYMENTS</h2>
        </div>
        <p>
          If a user is charged more than once for the same transaction due to technical
          errors or payment gateway issues, SoilnWater may refund the duplicate amount
          after verification.
        </p>
        <p>Users must notify SoilnWater within seven (7) days of the transaction.</p>
      </section>

      <section class="refund-policy-section">
        <div class="refund-section-heading">
          <span>06</span>
          <h2>FAILED TRANSACTIONS</h2>
        </div>
        <p>If payment is deducted but the service is not activated due to technical reasons:</p>
        <ul class="refund-check-list">
          <li>The transaction may be automatically reversed by the payment gateway or banking institution; or</li>
          <li>SoilnWater may process the activation manually; or</li>
          <li>A refund may be issued after verification.</li>
        </ul>
        <p>Processing times may vary depending on banks and payment service providers.</p>
      </section>

      <section class="refund-policy-section">
        <div class="refund-section-heading">
          <span>07</span>
          <h2>PLATFORM ERROR REFUNDS</h2>
        </div>
        <p>Refunds may be considered if:</p>
        <ul class="refund-check-list">
          <li>Paid services cannot be delivered due to a verified Platform error;</li>
          <li>Membership activation permanently fails due to SoilnWater's technical issues;</li>
          <li>Purchased services become unavailable due to a Platform malfunction.</li>
        </ul>
        <p>Approval of refunds shall be solely at SoilnWater's discretion after investigation.</p>
      </section>

      <section class="refund-policy-section">
        <div class="refund-section-heading">
          <span>08</span>
          <h2>NON-REFUNDABLE SERVICES</h2>
        </div>
        <p>The following are generally non-refundable:</p>
        <ul class="refund-check-list two-column">
          <li>Premium Membership fees;</li>
          <li>Featured Listings;</li>
          <li>Sponsored Profiles;</li>
          <li>Advertising Packages;</li>
          <li>Promotional Campaigns;</li>
          <li>Profile Enhancement Services;</li>
          <li>Verification Fees;</li>
          <li>Business Promotion Services;</li>
          <li>Marketing Services;</li>
          <li>Consultancy Listing Upgrades;</li>
          <li>Service Provider Listing Upgrades;</li>
          <li>Vendor Listing Upgrades.</li>
        </ul>
      </section>

      <section class="refund-policy-section">
        <div class="refund-section-heading">
          <span>09</span>
          <h2>USER ACCOUNT TERMINATION</h2>
        </div>
        <p>No refund shall be provided if an account is suspended or terminated due to:</p>
        <ul class="refund-check-list two-column">
          <li>Violation of Platform policies;</li>
          <li>Fraudulent activity;</li>
          <li>Misrepresentation;</li>
          <li>Illegal conduct;</li>
          <li>Abuse of Platform services.</li>
        </ul>
      </section>

      <section class="refund-policy-section" id="refund-process">
        <div class="refund-section-heading">
          <span>10</span>
          <h2>REFUND REQUEST PROCESS</h2>
        </div>
        <p>Refund requests may be submitted through:</p>
        <p><strong>Email:</strong> [Insert Official Email]</p>
        <p>The request should include:</p>
        <ul class="refund-check-list two-column">
          <li>Full Name;</li>
          <li>Registered Email Address;</li>
          <li>Mobile Number;</li>
          <li>Transaction ID;</li>
          <li>Payment Date;</li>
          <li>Reason for Refund Request;</li>
          <li>Supporting Documents, if applicable.</li>
        </ul>
        <p>Submission of a refund request does not guarantee approval.</p>
      </section>

      <section class="refund-policy-section">
        <div class="refund-section-heading">
          <span>11</span>
          <h2>REFUND PROCESSING</h2>
        </div>
        <p>Approved refunds shall be processed using the original payment method wherever feasible.</p>
        <p>Refund timelines may vary depending on:</p>
        <ul class="refund-check-list two-column">
          <li>Banking institutions;</li>
          <li>Payment gateways;</li>
          <li>Payment processors;</li>
          <li>Regulatory requirements.</li>
        </ul>
        <p>Typical processing time may range from 7 to 15 business days after approval.</p>
      </section>

      <section class="refund-policy-section">
        <div class="refund-section-heading">
          <span>12</span>
          <h2>CANCELLATION OF FREE ACCOUNTS</h2>
        </div>
        <p>Users may discontinue use of free accounts at any time.</p>
        <p>Users may request account deletion in accordance with applicable Platform policies and legal obligations.</p>
        <p>Certain records may be retained as required by law.</p>
      </section>

      <section class="refund-policy-section">
        <div class="refund-section-heading">
          <span>13</span>
          <h2>FORCE MAJEURE</h2>
        </div>
        <p>
          SoilnWater shall not be liable for service interruptions, delays, or inability
          to provide services caused by events beyond its reasonable control, including:
        </p>
        <ul class="refund-check-list two-column">
          <li>Natural disasters;</li>
          <li>Government actions;</li>
          <li>Internet outages;</li>
          <li>Cyber incidents;</li>
          <li>Power failures;</li>
          <li>Pandemics;</li>
          <li>Labor disputes.</li>
        </ul>
        <p>No refund shall be payable for disruptions arising from such events.</p>
      </section>

      <section class="refund-policy-section">
        <div class="refund-section-heading">
          <span>14</span>
          <h2>CHANGES TO THIS POLICY</h2>
        </div>
        <p>SoilnWater reserves the right to modify this Policy at any time.</p>
        <p>Updated versions shall become effective immediately upon publication on the Platform.</p>
        <p>Continued use of the Platform constitutes acceptance of revised terms.</p>
      </section>

      <section class="refund-policy-section">
        <div class="refund-section-heading">
          <span>15</span>
          <h2>GOVERNING LAW</h2>
        </div>
        <p>This Policy shall be governed by the laws of India.</p>
        <p>
          Any disputes arising from this Policy shall be subject to the exclusive
          jurisdiction of the courts located in Dehradun, Uttarakhand, India.
        </p>
      </section>

      <section class="refund-policy-section refund-contact-section" id="contact-information">
        <div class="refund-section-heading">
          <span>16</span>
          <h2>CONTACT INFORMATION</h2>
        </div>
        <p>
          Annuvedant Electronics (OPC) Private Limited<br>
          Owner of SoilnWater
        </p>
        <div class="refund-contact-grid">
          <div><i class="fa-solid fa-envelope"></i><strong>Email:</strong> soilnwaterworld@gmail.com</div>
          <div><i class="fa-solid fa-phone"></i><strong>Phone:</strong> 7055533011</div>
          <div><i class="fa-solid fa-location-dot"></i><strong>Address:</strong> 102, 16-E Old Survey Road, Dehradun, Uttarakhand</div>
        </div>
        <p>
          By purchasing any paid service on SoilnWater, users acknowledge that they have
          read, understood, and agreed to this Refund &amp; Cancellation Policy.
        </p>
      </section>
    </div>
  </main>
</div>
@endsection
