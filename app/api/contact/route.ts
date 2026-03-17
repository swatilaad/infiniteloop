import { NextResponse } from 'next/server';

export async function POST(request: Request) {
  try {
    const body = await request.json();
    const { name, email, message } = body;

    if (!name || !email || !message) {
      return NextResponse.json({ error: 'Missing required fields' }, { status: 400 });
    }

    // ── Option A: Forward to WordPress REST API contact endpoint ─────────────
    // If you have a WP contact plugin (e.g. Contact Form 7 REST API, WPForms),
    // replace the URL below with your endpoint.
    //
    // const wpRes = await fetch(`${process.env.NEXT_PUBLIC_WP_BASE_URL}/wp-json/contact-form-7/v1/contact-forms/1/feedback`, {
    //   method: 'POST',
    //   headers: { 'Content-Type': 'application/json' },
    //   body: JSON.stringify({ name, email, message }),
    // });

    // ── Option B: Send via SMTP using nodemailer (add nodemailer package) ─────
    // const transporter = nodemailer.createTransport({ ... });
    // await transporter.sendMail({ ... });

    // ── Current: Log and return success (replace with real handler above) ─────
    console.log('Contact form submission:', { name, email, message });

    return NextResponse.json({ success: true }, { status: 200 });
  } catch {
    return NextResponse.json({ error: 'Internal server error' }, { status: 500 });
  }
}
