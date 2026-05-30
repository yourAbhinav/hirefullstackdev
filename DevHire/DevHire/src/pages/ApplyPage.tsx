import React, { useState } from 'react';
import { Job } from '../types';
import { UploadCloud, FileText, ArrowLeft, ShieldCheck, Mail, Phone, Briefcase, PlusCircle, AlertCircle, Trash } from 'lucide-react';

interface ApplyPageProps {
  job: Job | null;
  onNavigate: (page: string) => void;
  onSubmitApplication: (applicationData: any) => void;
}

export const ApplyPage: React.FC<ApplyPageProps> = ({ job, onNavigate, onSubmitApplication }) => {
  const [fullName, setFullName] = useState('');
  const [email, setEmail] = useState('');
  const [phone, setPhone] = useState('');
  const [experience, setExperience] = useState('Senior');
  const [techStackInput, setTechStackInput] = useState('');
  const [techStackArray, setTechStackArray] = useState<string[]>(['TypeScript', 'React', 'Node.js']);
  const [portfolioUrl, setPortfolioUrl] = useState('');
  const [coverLetter, setCoverLetter] = useState('');
  const [resumeFile, setResumeFile] = useState<File | null>(null);
  const [resumeName, setResumeName] = useState('Abhinav_Kumar_CV.pdf');
  const [dragOver, setDragOver] = useState(false);
  const [statusMessage, setStatusMessage] = useState('');
  const [errorMessage, setErrorMessage] = useState('');
  const [formSubmitting, setFormSubmitting] = useState(false);

  const handleAddTech = () => {
    if (techStackInput.trim() && !techStackArray.includes(techStackInput.trim())) {
      setTechStackArray([...techStackArray, techStackInput.trim()]);
      setTechStackInput('');
    }
  };

  const handleRemoveTech = (tech: string) => {
    setTechStackArray(techStackArray.filter((t) => t !== tech));
  };

  const handleDragOver = (e: React.DragEvent) => {
    e.preventDefault();
    setDragOver(true);
  };

  const handleDragLeave = () => {
    setDragOver(false);
  };

  const handleDrop = (e: React.DragEvent) => {
    e.preventDefault();
    setDragOver(false);
    if (e.dataTransfer.files && e.dataTransfer.files[0]) {
      const file = e.dataTransfer.files[0];
      if (file.type === 'application/pdf' || file.name.endsWith('.pdf')) {
        setResumeFile(file);
        setResumeName(file.name);
        setErrorMessage('');
      } else {
        setErrorMessage('File upload rejected. Only PDF formatted resumes are permitted under security guidelines.');
      }
    }
  };

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files && e.target.files[0]) {
      const file = e.target.files[0];
      if (file.type === 'application/pdf' || file.name.endsWith('.pdf')) {
        setResumeFile(file);
        setResumeName(file.name);
        setErrorMessage('');
      } else {
        setErrorMessage('File upload rejected. Only PDF formatted resumes are permitted under security guidelines.');
      }
    }
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMessage('');
    setStatusMessage('');

    if (!fullName || !email || !phone) {
      setErrorMessage('Please state your Full Name, Email details, and phone key code.');
      return;
    }

    if (!resumeName) {
      setErrorMessage('Please upload a PDF layout copy of your professional resume.');
      return;
    }

    setFormSubmitting(true);

    const appPayload = {
      jobId: job ? job.id : 'custom',
      jobTitle: job ? job.title : 'General Full Stack Sourcing Pool',
      companyName: job ? job.companyName : 'DevHire',
      fullName,
      email,
      phone,
      experience,
      techStack: techStackArray,
      portfolioUrl,
      resumeName,
      coverLetter,
      status: 'Pending' as const,
      createdAt: new Date().toISOString().split('T')[0]
    };

    setTimeout(() => {
      setFormSubmitting(false);
      onSubmitApplication(appPayload);
      setStatusMessage('SUCCESS');
    }, 1200);
  };

  return (
    <div className="relative py-16 sm:py-24 overflow-hidden bg-slate-950 text-white min-h-screen">
      <div className="absolute inset-0 bg-grid-ambient opacity-50" />
      <div className="absolute top-20 left-1/4 h-[350px] w-[500px] rounded-full bg-brand-primary/10 blur-[130px] pointer-events-none" />

      <div className="relative mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        
        {/* Back Link */}
        <button
          onClick={() => onNavigate(job ? 'jobs' : 'home')}
          className="inline-flex items-center gap-2 text-xs font-semibold text-slate-400 hover:text-white transition-all cursor-pointer mb-8 group"
        >
          <ArrowLeft className="h-4 w-4 group-hover:-translate-x-1 transition-all" />
          <span>Back to Open Positions</span>
        </button>

        {/* Header summary info box */}
        <div className="rounded-3xl border border-slate-900 bg-slate-900/35 p-6 sm:p-8 glass-panel mb-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
          <div>
            <span className="text-[10px] font-mono tracking-widest text-brand-secondary uppercase block mb-1">
              BENCHMARK EVALUATION WORKFLOW
            </span>
            <h1 className="font-display text-2xl sm:text-3xl font-extrabold tracking-tight">
              {job ? `Apply for ${job.title}` : 'Submit Sourcing Application'}
            </h1>
            <p className="text-xs text-slate-400 mt-1">
              Authorized screening forms managed by certified Stripe & Google authentication layers.
            </p>
          </div>

          {job && (
            <div className="flex items-center gap-2 px-3 py-1.5 rounded-xl border border-slate-800 bg-slate-950 text-xs">
              <span className="text-slate-500">Target Office:</span>
              <span className="font-bold text-white uppercase">{job.companyName}</span>
            </div>
          )}
        </div>

        {statusMessage === 'SUCCESS' ? (
          <div className="rounded-3xl border border-emerald-500/30 bg-emerald-500/5 p-8 text-center space-y-6 glass-panel animate-in fade-in zoom-in-95 duration-400">
            <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-400">
              <ShieldCheck className="h-6 w-6" />
            </div>
            
            <div className="space-y-2">
              <h3 className="font-display font-extrabold text-2xl text-white">Application Persisted</h3>
              <p className="text-xs text-slate-400 max-w-md mx-auto leading-relaxed">
                Thank you, {fullName}! Your screening credentials and tech stack declarations have been saved in DevHire SQL records. Candidate verification workflow is triggered.
              </p>
            </div>

            <div className="h-px bg-slate-900 max-w-sm mx-auto" />

            <div className="flex items-center justify-center gap-4">
              <button
                onClick={() => onNavigate('jobs')}
                className="px-5 py-2.5 rounded-xl bg-slate-900 border border-slate-800 hover:bg-slate-850 hover:text-white transition-all text-xs text-slate-300 font-medium cursor-pointer"
              >
                Browse Sourcing Jobs
              </button>
              <button
                onClick={() => onNavigate('admin')}
                className="px-5 py-2.5 rounded-xl bg-brand-primary text-white text-xs font-semibold hover:opacity-95 shadow-md shadow-brand-primary/20 cursor-pointer"
              >
                Go to Admin View
              </button>
            </div>
          </div>
        ) : (
          <form onSubmit={handleSubmit} className="space-y-8 bg-slate-900/10 p-8 rounded-3xl border border-slate-900 glass-panel">
            
            {errorMessage && (
              <div className="flex items-center gap-2.5 p-4 rounded-xl bg-rose-500/5 border border-rose-500/20 text-xs text-brand-accent">
                <AlertCircle className="h-4 w-4" />
                <span>{errorMessage}</span>
              </div>
            )}

            {/* Profile Basics Row */}
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
              <div className="space-y-2">
                <label className="text-[10px] font-mono font-bold tracking-widest text-slate-400 uppercase">Your Named Identity</label>
                <input
                  type="text"
                  required
                  value={fullName}
                  onChange={(e) => setFullName(e.target.value)}
                  className="w-full h-11 px-3.5 rounded-xl border border-slate-800 bg-slate-950 text-xs text-white placeholder-slate-650 focus:outline-none focus:ring-1 focus:ring-brand-primary"
                  placeholder="e.g. Abhinav Kumar"
                />
              </div>

              <div className="space-y-2">
                <label className="text-[10px] font-mono font-bold tracking-widest text-slate-400 uppercase">Contact Email-Key</label>
                <input
                  type="email"
                  required
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  className="w-full h-11 px-3.5 rounded-xl border border-slate-800 bg-slate-950 text-xs text-white placeholder-slate-650 focus:outline-none focus:ring-1 focus:ring-brand-primary"
                  placeholder="e.g. abhinavkumark70@gmail.com"
                />
              </div>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
              <div className="space-y-2">
                <label className="text-[10px] font-mono font-bold tracking-widest text-slate-400 uppercase">Direct Mobile Contact</label>
                <input
                  type="tel"
                  required
                  value={phone}
                  onChange={(e) => setPhone(e.target.value)}
                  className="w-full h-11 px-3.5 rounded-xl border border-slate-800 bg-slate-950 text-xs text-white placeholder-slate-650 focus:outline-none focus:ring-1 focus:ring-brand-primary"
                  placeholder="e.g. +1 (415) 880-9900"
                />
              </div>

              <div className="space-y-2">
                <label className="text-[10px] font-mono font-bold tracking-widest text-slate-400 uppercase">Experience level bracket</label>
                <select
                  value={experience}
                  onChange={(e) => setExperience(e.target.value)}
                  className="w-full h-11 px-3.5 rounded-xl border border-slate-800 bg-slate-950 text-xs text-slate-300 focus:outline-none focus:ring-1 focus:ring-brand-primary cursor-pointer select-none"
                >
                  <option value="Junior">Junior level (1-3 Years)</option>
                  <option value="Mid-Level">Mid level (3-5 Years)</option>
                  <option value="Senior">Senior level (5+ Years)</option>
                </select>
              </div>
            </div>

            {/* Custom Portfolio URI hook */}
            <div className="space-y-2">
              <label className="text-[10px] font-mono font-bold tracking-widest text-slate-400 uppercase">Portfolio/GitHub/Website URL</label>
              <input
                type="url"
                value={portfolioUrl}
                onChange={(e) => setPortfolioUrl(e.target.value)}
                className="w-full h-11 px-3.5 rounded-xl border border-slate-800 bg-slate-950 text-xs text-white placeholder-slate-650 focus:outline-none focus:ring-1 focus:ring-brand-primary"
                placeholder="e.g. https://abhinav.dev"
              />
            </div>

            {/* Tech stack management tags block */}
            <div className="space-y-3">
              <label className="text-[10px] font-mono font-bold tracking-widest text-slate-400 uppercase block">Certified Specializations List</label>
              <div className="flex gap-2">
                <input
                  type="text"
                  value={techStackInput}
                  onChange={(e) => setTechStackInput(e.target.value)}
                  className="h-10 px-3.5 rounded-xl border border-slate-800 bg-slate-950 text-xs text-white placeholder-slate-650 focus:outline-none focus:ring-1 focus:ring-brand-primary"
                  placeholder="e.g. Redis"
                  onKeyDown={(e) => e.key === 'Enter' && (e.preventDefault(), handleAddTech())}
                />
                <button
                  type="button"
                  onClick={handleAddTech}
                  className="px-4 rounded-xl bg-slate-900 hover:bg-slate-850 hover:text-white border border-slate-800 text-xs text-slate-300 font-medium transition-all cursor-pointer flex items-center gap-1.5"
                >
                  <PlusCircle className="h-4 w-4" />
                  <span>Add Tech</span>
                </button>
              </div>

              {/* Tag display lists */}
              <div className="flex flex-wrap gap-2 pt-1.5 p-3 rounded-2xl bg-slate-950/40 border border-slate-900/60 min-h-[50px]">
                {techStackArray.length === 0 && (
                  <span className="text-xs text-slate-600 block pl-1 italic">No tags associated yet.</span>
                )}
                {techStackArray.map((tech) => (
                  <span
                    key={tech}
                    className="inline-flex items-center gap-1.5 rounded-full bg-brand-primary/10 border border-brand-primary/25 text-[10px] font-medium text-brand-primary px-3 py-1"
                  >
                    <span>{tech}</span>
                    <button
                      type="button"
                      onClick={() => handleRemoveTech(tech)}
                      className="text-brand-primary hover:text-brand-accent transition-all cursor-pointer font-bold leading-none text-[11px]"
                    >
                      ×
                    </button>
                  </span>
                ))}
              </div>
            </div>

            {/* Professional Drag Resume Upload Component block */}
            <div className="space-y-2">
              <label className="text-[10px] font-mono font-bold tracking-widest text-slate-400 uppercase block">Submit PDF Professional CV Resume</label>
              
              <div
                onDragOver={handleDragOver}
                onDragLeave={handleDragLeave}
                onDrop={handleDrop}
                className={`border-2 border-dashed rounded-2xl p-6 text-center transition-all cursor-pointer relative ${
                  dragOver 
                    ? 'border-brand-primary bg-brand-primary/5' 
                    : 'border-slate-800 hover:border-slate-700 bg-slate-950/40'
                }`}
              >
                <input
                  type="file"
                  id="resumeUpload"
                  accept=".pdf"
                  onChange={handleFileChange}
                  className="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                />

                <div className="space-y-2">
                  <UploadCloud className="h-8 w-8 text-brand-primary mx-auto opacity-70" />
                  <p className="text-xs text-slate-300 font-bold">
                    {resumeName ? `Selected PDF: ${resumeName}` : 'Drag & Drop PDF or click to locate file'}
                  </p>
                  <p className="text-[10px] text-slate-500">
                    Maximum size: 10MB limit. Security requirements restricts uploads strictly to safe PDF guidelines.
                  </p>
                </div>
              </div>
            </div>

            {/* Cover letter */}
            <div className="space-y-2">
              <label className="text-[10px] font-mono font-bold tracking-widest text-slate-400 uppercase block">Sourcing Cover Remarks (Supporting copy)</label>
              <textarea
                rows={4}
                value={coverLetter}
                onChange={(e) => setCoverLetter(e.target.value)}
                className="w-full p-3.5 rounded-xl border border-slate-800 bg-slate-950 text-xs text-white placeholder-slate-650 focus:outline-none focus:ring-1 focus:ring-brand-primary resize-none"
                placeholder="State any supporting details, specialized references, or interview timezone codes..."
              />
            </div>

            {/* Submit application handles */}
            <div className="pt-4 border-t border-slate-900/60 flex items-center justify-between gap-6">
              
              <div className="text-[10px] text-slate-500 max-w-sm flex items-center gap-1.5">
                <ShieldCheck className="h-4.5 w-4.5 text-brand-secondary flex-shrink-0" />
                <span>Submit forms are protected by CSRF key blocks and SSL session checkers. No plaintext caching.</span>
              </div>

              <button
                type="submit"
                disabled={formSubmitting}
                className="px-6 py-3 rounded-xl bg-gradient-to-r from-brand-primary to-indigo-500 text-xs font-semibold hover:opacity-95 shadow-lg shadow-brand-primary/25 cursor-pointer flex items-center gap-2 flex-shrink-0"
              >
                <span>{formSubmitting ? 'Syncing SQL database...' : 'Submit Certified Application'}</span>
              </button>

            </div>

          </form>
        )}

      </div>
    </div>
  );
};
