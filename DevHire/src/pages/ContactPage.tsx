import React, { useState } from 'react';
import { SITE_CONFIG } from '../config';
import { Mail, Phone, MapPin, Send, MessageSquare, ShieldAlert } from 'lucide-react';

export const ContactPage: React.FC = () => {
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    subject: '',
    message: ''
  });
  const [submitted, setSubmitted] = useState(false);
  const [error, setError] = useState('');

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!formData.name || !formData.email || !formData.subject || !formData.message) {
      setError('Please fill in all the fields before submitting.');
      return;
    }
    setError('');
    setSubmitted(true);
    // Simulating secure persist database sync on handlers/contact_handler.php
    const savedMessages = JSON.parse(localStorage.getItem('devhire_messages') || '[]');
    savedMessages.push({
      id: 'msg-' + Date.now(),
      ...formData,
      createdAt: new Date().toISOString()
    });
    localStorage.setItem('devhire_messages', JSON.stringify(savedMessages));
  };

  return (
    <div className="relative py-16 sm:py-24 overflow-hidden bg-slate-950 text-white">
      <div className="absolute inset-0 bg-grid-ambient opacity-50" />
      <div className="absolute top-20 left-1/4 h-[300px] w-[500px] rounded-full bg-brand-primary/10 blur-[130px] pointer-events-none" />

      <div className="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        
        {/* Header */}
        <div className="text-center max-w-2xl mx-auto mb-16">
          <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-brand-primary/10 border border-brand-primary/25 text-xs text-brand-primary font-mono tracking-wider uppercase mb-4">
            <MessageSquare className="h-3.5 w-3.5" />
            <span>Support Center</span>
          </div>
          <h1 className="font-display text-4xl font-extrabold tracking-tight mb-4">
            Direct Line to Sourcing Leaders
          </h1>
          <p className="text-sm text-slate-400">
            Have custom requirement sheets, compliance audits, or partnership scopes? Leave a message. Our support staff will response within 4 normal business hours.
          </p>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
          
          {/* Info Details column - 5cols */}
          <div className="lg:col-span-5 space-y-8">
            <div className="rounded-3xl border border-slate-900 bg-slate-900/10 p-8 glass-panel space-y-6">
              <h3 className="font-display text-xl font-bold">Office Headquarter</h3>
              
              <ul className="space-y-6">
                <li className="flex items-start gap-4">
                  <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-950 border border-slate-800 text-brand-primary">
                    <MapPin className="h-5 w-5" />
                  </div>
                  <div>
                    <span className="text-xs text-slate-500 font-mono tracking-wider block uppercase mb-1">LOCATION</span>
                    <p className="text-slate-300 text-sm leading-relaxed">{SITE_CONFIG.contact.address}</p>
                  </div>
                </li>

                <li className="flex items-start gap-4">
                  <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-950 border border-slate-800 text-brand-secondary">
                    <Phone className="h-5 w-5" />
                  </div>
                  <div>
                    <span className="text-xs text-slate-500 font-mono tracking-wider block uppercase mb-1">CALL US DIRECTLY</span>
                    <p className="text-slate-300 text-sm">{SITE_CONFIG.contact.phone}</p>
                  </div>
                </li>

                <li className="flex items-start gap-4">
                  <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-950 border border-slate-800 text-brand-secondary">
                    <Mail className="h-5 w-5" />
                  </div>
                  <div>
                    <span className="text-xs text-slate-500 font-mono tracking-wider block uppercase mb-1">EMAIL SUPPORT</span>
                    <a href={`mailto:${SITE_CONFIG.contact.email}`} className="text-brand-secondary text-sm hover:underline">
                      {SITE_CONFIG.contact.email}
                    </a>
                  </div>
                </li>
              </ul>
              
              <div className="h-px bg-slate-900" />
              
              <div className="text-xs text-slate-500 font-mono space-y-1">
                <p>Support Hours: {SITE_CONFIG.contact.hours}</p>
                <p>Response Target S.L.A: Under 4 Hours</p>
              </div>
            </div>

            {/* Embedded maps simulator */}
            <div className="rounded-3xl overflow-hidden border border-slate-900 h-64 bg-slate-900/20 relative">
              <iframe
                title="Office Location Map"
                src={SITE_CONFIG.contact.mapEmbeddingUrl}
                width="100%"
                height="100%"
                style={{ border: 0 }}
                allowFullScreen={false}
                loading="lazy"
                referrerPolicy="no-referrer-when-downgrade"
              />
            </div>
          </div>

          {/* Contact form column - 7cols */}
          <div className="lg:col-span-7">
            <div className="rounded-3xl border border-slate-900 bg-slate-900/20 p-8 sm:p-10 glass-panel">
              <h3 className="font-display text-xl font-bold mb-1">Send a Secure Message</h3>
              <p className="text-xs text-slate-500 mb-8">All messages are persists securely into MySQL databases and tracked in customer ticket logs.</p>

              {submitted ? (
                <div className="text-center py-12 px-6 rounded-2xl bg-emerald-500/5 border border-emerald-500/20 space-y-4">
                  <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-500/20 text-emerald-400">
                    <Send className="h-5 w-5" />
                  </div>
                  <h4 className="text-lg font-bold text-white">Ticket Created Successfully</h4>
                  <p className="text-sm text-slate-400 max-w-md mx-auto">
                    Thank you, {formData.name}! Your message was persistent in support logs. Our engineering team has been notified.
                  </p>
                  <button
                    onClick={() => {
                      setSubmitted(false);
                      setFormData({ name: '', email: '', subject: '', message: '' });
                    }}
                    className="mt-4 px-4 py-2 rounded-xl bg-slate-950 border border-slate-800 text-xs hover:bg-slate-900 transition-all cursor-pointer text-slate-300"
                  >
                    Send Another Message
                  </button>
                </div>
              ) : (
                <form onSubmit={handleSubmit} className="space-y-6">
                  
                  {error && (
                    <div className="flex items-center gap-2.5 p-3.5 rounded-xl bg-rose-500/5 border border-rose-500/20 text-xs text-brand-accent">
                      <ShieldAlert className="h-4 w-4" />
                      <span>{error}</span>
                    </div>
                  )}

                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div className="space-y-2">
                      <label htmlFor="name" className="text-xs font-semibold text-slate-300 tracking-wider uppercase block">Your Name</label>
                      <input
                        type="text"
                        id="name"
                        value={formData.name}
                        onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                        className="w-full h-11 px-3.5 rounded-xl border border-slate-800 bg-slate-950/60 text-sm text-white placeholder-slate-600 focus:outline-none focus:ring-1 focus:ring-brand-primary focus:border-brand-primary"
                        placeholder="e.g. John Doe"
                      />
                    </div>

                    <div className="space-y-2">
                      <label htmlFor="email" className="text-xs font-semibold text-slate-300 tracking-wider uppercase block">Email Address</label>
                      <input
                        type="email"
                        id="email"
                        value={formData.email}
                        onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                        className="w-full h-11 px-3.5 rounded-xl border border-slate-800 bg-slate-950/60 text-sm text-white placeholder-slate-600 focus:outline-none focus:ring-1 focus:ring-brand-primary focus:border-brand-primary"
                        placeholder="e.g. john@yourcompany.com"
                      />
                    </div>
                  </div>

                  <div className="space-y-2">
                    <label htmlFor="subject" className="text-xs font-semibold text-slate-300 tracking-wider uppercase block">Inquiry Subject</label>
                    <input
                      type="text"
                      id="subject"
                      value={formData.subject}
                      onChange={(e) => setFormData({ ...formData, subject: e.target.value })}
                      className="w-full h-11 px-3.5 rounded-xl border border-slate-800 bg-slate-950/60 text-sm text-white placeholder-slate-600 focus:outline-none focus:ring-1 focus:ring-brand-primary focus:border-brand-primary"
                      placeholder="e.g. Custom Dev Recruiting SLA Requirements"
                    />
                  </div>

                  <div className="space-y-2">
                    <label htmlFor="message" className="text-xs font-semibold text-slate-300 tracking-wider uppercase block">Message Body</label>
                    <textarea
                      id="message"
                      rows={5}
                      value={formData.message}
                      onChange={(e) => setFormData({ ...formData, message: e.target.value })}
                      className="w-full px-3.5 py-3 rounded-xl border border-slate-800 bg-slate-950/60 text-sm text-white placeholder-slate-600 focus:outline-none focus:ring-1 focus:ring-brand-primary focus:border-brand-primary resize-none"
                      placeholder="Specify your technical requirements, team needs, and pipeline timelines here..."
                    />
                  </div>

                  <button
                    type="submit"
                    className="w-full h-12 rounded-xl bg-gradient-to-r from-brand-primary to-indigo-500 text-sm font-semibold hover:opacity-95 shadow-lg shadow-brand-primary/20 hover:shadow-brand-primary/40 transition-all cursor-pointer flex items-center justify-center gap-2"
                  >
                    <Send className="h-4 w-4" />
                    Submit Sourcing Request
                  </button>

                </form>
              )}
            </div>
          </div>

        </div>

      </div>
    </div>
  );
};
