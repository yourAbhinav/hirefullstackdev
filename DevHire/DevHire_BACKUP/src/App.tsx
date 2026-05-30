import React, { useState, useEffect } from 'react';
import { Navbar } from './components/Navbar';
import { Footer } from './components/Footer';
import { HeroSection } from './components/HeroSection';
import { HowItWorks } from './pages/HowItWorks';
import { PricingPage } from './pages/PricingPage';
import { ContactPage } from './pages/ContactPage';
import { DevelopersPage } from './pages/DevelopersPage';
import { JobsPage } from './pages/JobsPage';
import { LoginPage } from './pages/LoginPage';
import { RegisterPage } from './pages/RegisterPage';
import { ApplyPage } from './pages/ApplyPage';
import { AdminDashboard } from './pages/AdminDashboard';
import { CompanyDashboard } from './pages/CompanyDashboard';
import { Job, Developer, Application, Testimonial } from './types';
import { 
  INITIAL_JOBS, 
  INITIAL_DEVELOPERS, 
  INITIAL_APPLICATIONS, 
  INITIAL_TESTIMONIALS 
} from './initialData';
import { HelpCircle, Shield, Sparkles } from 'lucide-react';

export default function App() {
  const [currentPage, setCurrentPage] = useState<string>('home');
  const [selectedJobForApply, setSelectedJobForApply] = useState<Job | null>(null);

  // Core Data States
  const [currentUser, setCurrentUser] = useState<{ name: string; email: string; role: 'developer' | 'company' | 'admin' } | null>(null);
  const [jobs, setJobs] = useState<Job[]>(INITIAL_JOBS);
  const [developers, setDevelopers] = useState<Developer[]>(INITIAL_DEVELOPERS);
  const [applications, setApplications] = useState<Application[]>(INITIAL_APPLICATIONS);
  const [testimonials] = useState<Testimonial[]>(INITIAL_TESTIMONIALS);

  // Load user details if persisted
  useEffect(() => {
    try {
      const persistedUser = localStorage.getItem('devhire_current_user');
      if (persistedUser) {
        setCurrentUser(JSON.parse(persistedUser));
      }

      // Load custom DB entries
      const customApps = localStorage.getItem('devhire_custom_applications');
      if (customApps) {
        setApplications(JSON.parse(customApps));
      } else {
        localStorage.setItem('devhire_custom_applications', JSON.stringify(INITIAL_APPLICATIONS));
      }
    } catch (e) {
      console.error(e);
    }
  }, []);

  // Update URL references when changing views
  const handleNavigation = (pageId: string) => {
    setCurrentPage(pageId);
    window.scrollTo({ top: 0, behavior: 'instant' });
  };

  const handleApplyForJob = (job: Job) => {
    setSelectedJobForApply(job);
    handleNavigation('apply');
  };

  const handleSetRole = (role: 'developer' | 'company' | 'admin' | null) => {
    if (role === null) {
      setCurrentUser(null);
      localStorage.removeItem('devhire_current_user');
      handleNavigation('home');
    } else {
      const mockEmail = role === 'admin' ? 'admin@devhire.com' : role === 'company' ? 'info@stripe.com' : 'abhinavkumark70@gmail.com';
      const mockName = role === 'admin' ? 'Admin Reviewer' : role === 'company' ? 'Stripe HR Lead' : 'Abhinav Kumar';
      const userObj = { name: mockName, email: mockEmail, role };
      setCurrentUser(userObj);
      localStorage.setItem('devhire_current_user', JSON.stringify(userObj));
    }
  };

  // Auth logins
  const handleLoginSuccess = (email: string, role: 'developer' | 'company' | 'admin') => {
    const isSpecialAdmin = email.toLowerCase() === 'admin@devhire.com';
    const finalRole = isSpecialAdmin ? 'admin' as const : role;
    
    // Derived names
    let derivedName = email.split('@')[0];
    derivedName = derivedName.charAt(0).toUpperCase() + derivedName.slice(1);
    
    const userObj = { name: derivedName, email, role: finalRole };
    setCurrentUser(userObj);
    localStorage.setItem('devhire_current_user', JSON.stringify(userObj));
  };

  const handleRegisterSuccess = (email: string, role: 'developer' | 'company') => {
    let derivedName = email.split('@')[0];
    derivedName = derivedName.charAt(0).toUpperCase() + derivedName.slice(1);
    
    const userObj = { name: derivedName, email, role };
    setCurrentUser(userObj);
    localStorage.setItem('devhire_current_user', JSON.stringify(userObj));
  };

  const handleLogout = () => {
    setCurrentUser(null);
    localStorage.removeItem('devhire_current_user');
    handleNavigation('home');
  };

  // DB Modification simulated callbacks
  const handleAddApplication = (newApp: any) => {
    const updated = [newApp, ...applications];
    setApplications(updated);
    localStorage.setItem('devhire_custom_applications', JSON.stringify(updated));
    
    // Also add to developer profile lists if registered as developer
    const isExistDev = developers.some(d => d.email === newApp.email);
    if (!isExistDev) {
      const newDev: Developer = {
        id: 'dev-' + Date.now(),
        fullName: newApp.fullName,
        email: newApp.email,
        phone: newApp.phone,
        title: 'Associated Candidate',
        experience: newApp.experience,
        techStack: newApp.techStack,
        portfolioUrl: newApp.portfolioUrl,
        bio: newApp.coverLetter || 'Certified full stack talent added via application sweeps.',
        verified: false,
        location: 'United States'
      };
      const updatedDevs = [...developers, newDev];
      setDevelopers(updatedDevs);
    }
  };

  const handleUpdateApplicationStatus = (id: string, status: 'Approved' | 'Rejected') => {
    const updated = applications.map((app) => 
      app.id === id ? { ...app, status } : app
    );
    setApplications(updated);
    localStorage.setItem('devhire_custom_applications', JSON.stringify(updated));
  };

  const handleDeleteApplication = (id: string) => {
    const updated = applications.filter((app) => app.id !== id);
    setApplications(updated);
    localStorage.setItem('devhire_custom_applications', JSON.stringify(updated));
  };

  // Policies placeholders
  const renderSimpleDocPage = (title: string, desc: string, md: string) => {
    return (
      <div className="relative py-16 sm:py-24 bg-slate-950 text-white min-h-[75vh]">
        <div className="absolute inset-0 bg-grid-ambient opacity-50" />
        <div className="relative mx-auto max-w-4xl px-4">
          <div className="rounded-3xl border border-slate-900 bg-slate-900/10 p-8 sm:p-12 glass-panel">
            <h1 className="font-display text-4xl font-extrabold tracking-tight mb-4">{title}</h1>
            <p className="text-brand-secondary text-xs font-mono tracking-wider mb-8 uppercase">Last updated: May 2026</p>
            
            <div className="text-slate-350 text-sm leading-relaxed space-y-6">
              <p className="font-bold text-white text-base">{desc}</p>
              <div className="h-px bg-slate-900 my-4" />
              <p>For fully compliant production environments, this license governs developer credential storage, SOC2 sessions, cookies scopes, cookie tokens, and reference checking verification steps. All data is persisted securely via encrypted database tables.</p>
              <p>Failure to implement Terms validation on server environments poses secure liabilities of candidate tracking, as described strictly in our production-ready checklist sweeps.</p>
            </div>
          </div>
        </div>
      </div>
    );
  };

  return (
    <div className="relative min-h-screen bg-slate-950 font-sans antialiased text-slate-300 flex flex-col justify-between">
      
      {/* Dynamic Header */}
      <Navbar 
        currentPage={currentPage} 
        onNavigate={handleNavigation} 
        currentUser={currentUser}
        onLogout={handleLogout}
        onSetRole={handleSetRole}
      />

      {/* Main Routed Area component */}
      <main className="flex-grow">
        
        {/* State Sorter router */}
        {currentPage === 'home' && (
          <HeroSection onNavigate={handleNavigation} testimonials={testimonials} />
        )}
        
        {currentPage === 'jobs' && (
          <JobsPage jobs={jobs} onApplyForJob={handleApplyForJob} />
        )}

        {currentPage === 'developers' && (
          <DevelopersPage developers={developers} />
        )}

        {currentPage === 'how-it-works' && (
          <HowItWorks />
        )}

        {currentPage === 'pricing' && (
          <PricingPage onNavigate={handleNavigation} />
        )}

        {currentPage === 'contact' && (
          <ContactPage />
        )}

        {currentPage === 'login' && (
          <LoginPage onLoginSuccess={handleLoginSuccess} onNavigate={handleNavigation} />
        )}

        {currentPage === 'register' && (
          <RegisterPage onRegisterSuccess={handleRegisterSuccess} onNavigate={handleNavigation} />
        )}

        {currentPage === 'apply' && (
          <ApplyPage 
            job={selectedJobForApply} 
            onNavigate={handleNavigation} 
            onSubmitApplication={handleAddApplication}
          />
        )}

        {/* Dashboard Panels */}
        {currentPage === 'admin' && (
          <AdminDashboard 
            applications={applications} 
            onUpdateStatus={handleUpdateApplicationStatus}
            onDeleteApplication={handleDeleteApplication}
          />
        )}

        {currentPage === 'company' && (
          <CompanyDashboard 
            applications={applications} 
            onUpdateStatus={handleUpdateApplicationStatus}
          />
        )}

        {/* Policy document views */}
        {currentPage === 'privacy' && renderSimpleDocPage(
          'Privacy Policy Guidelines',
          'Sourcing privacy guarantees and SOC2 database audit protection.',
          ''
        )}

        {currentPage === 'terms' && renderSimpleDocPage(
          'Terms of Service Agreement',
          'Mandatory terms acceptance rules for both developer candidates and employer companies.',
          ''
        )}

        {currentPage === 'cookies' && renderSimpleDocPage(
          'Cookie Policy License',
          'Encrypted SSO cookies session tracking guidelines.',
          ''
        )}

      </main>

      {/* Persistent global floating prompt info trigger for simplified testing validation */}
      <div className="fixed bottom-6 right-6 z-50 flex flex-col items-end gap-2.5">
        <div className="group relative">
          <div className="hidden group-hover:block absolute bottom-full right-0 mb-3 w-[330px] rounded-2xl bg-slate-900 border border-slate-800 p-4 shadow-2xl glass-panel animate-in fade-in slide-in-from-bottom-2">
            <span className="text-[9px] font-mono font-bold tracking-widest text-brand-secondary block mb-1">
              SYSTEM CONSOLE QUICK GUIDE
            </span>
            <h3 className="font-display font-extrabold text-xs text-white mb-2">Test All Dynamic Scopes Fast</h3>
            
            <div className="text-[11px] text-slate-400 space-y-1.5 font-sans leading-relaxed">
              <p>• Click the <b className="text-indigo-400">profile button</b> on header to toggle fast roles between Developer, Company, or Super Admin reviews.</p>
              <p>• Apply for jobs as a developer to see candidate list increments.</p>
              <p>• Log in as <code className="text-brand-secondary font-mono">admin@devhire.com</code> (<code className="text-slate-350">admin123</code>) to review/approve candidates.</p>
              <p>• Try <b className="text-cyan-400">Company Dashboard</b> to test the 2-recipients-per-page paginator controls.</p>
            </div>
          </div>
          <button className="flex h-11 w-11 items-center justify-center rounded-full bg-slate-900 hover:bg-slate-800 border border-slate-800 shadow-xl text-brand-secondary cursor-pointer ring-2 ring-brand-secondary/20 hover:scale-105 transition-all">
            <HelpCircle className="h-5 w-5 animate-pulse" />
          </button>
        </div>
      </div>

      {/* Global Brand Footer */}
      <Footer onNavigate={handleNavigation} />

    </div>
  );
}
