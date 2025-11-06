import React from "react";

// Minimal frontpage content component
// - No header / no sidebar — just the page content area
// - Uses Tailwind CSS utility classes
// - Self-contained, responsive, accessible

export default function Test() {
  return (
    <main className="min-h-screen bg-gradient-to-b from-white to-gray-50 flex flex-col items-center justify-start py-16 px-6 sm:px-8 lg:px-20">
      {/* Hero */}
      <section className="w-full max-w-5xl text-center">
        <div className="mx-auto max-w-3xl">
          <p className="inline-block text-sm font-semibold uppercase tracking-wide text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full">
            New
          </p>

          <h1 className="mt-6 text-3xl sm:text-4xl lg:text-5xl font-extrabold leading-tight text-gray-900">
            Clean, focused frontpage layout
          </h1>

          <p className="mt-4 text-gray-600 text-base sm:text-lg">
            A minimal content-first frontpage component designed to be dropped into any React + Tailwind app. Simple hero,
            features, and call-to-action — no header or sidebar included.
          </p>

          <div className="mt-8 flex flex-col sm:flex-row justify-center gap-4">
            <button
              className="inline-flex items-center justify-center rounded-2xl px-6 py-3 bg-indigo-600 text-white font-semibold shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
              onClick={() => alert('Primary CTA clicked')}
            >
              Get started
            </button>

            <button
              className="inline-flex items-center justify-center rounded-2xl px-6 py-3 bg-white border border-gray-200 text-gray-700 font-medium hover:bg-gray-50 focus:outline-none"
              onClick={() => alert('Learn more clicked')}
            >
              Learn more
            </button>
          </div>
        </div>
      </section>

      {/* Features */}
      <section className="w-full max-w-5xl mt-14">
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          {[
            { title: 'Fast to build', desc: 'Small, composable, easy to style with Tailwind.' },
            { title: 'Accessible', desc: 'Keyboard friendly and semantic markup.' },
            { title: 'Responsive', desc: 'Looks great on phones, tablets and desktops.' },
            { title: 'Focused copy', desc: 'Designed for clarity and quick scanning.' },
            { title: 'Productive', desc: 'Provides a starting point for landing pages.' },
            { title: 'Customizable', desc: 'Swap colors, spacing, or layout in seconds.' },
          ].map((f) => (
            <article key={f.title} className="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
              <h3 className="text-lg font-semibold text-gray-900">{f.title}</h3>
              <p className="mt-2 text-sm text-gray-600">{f.desc}</p>
            </article>
          ))}
        </div>
      </section>

      {/* Newsletter / CTA strip */}
      <section className="w-full max-w-4xl mt-12">
        <div className="rounded-2xl bg-indigo-600/5 border border-indigo-100 p-6 sm:p-8 flex flex-col sm:flex-row items-center gap-4">
          <div className="flex-1">
            <h4 className="text-lg font-semibold text-gray-900">Stay in the loop</h4>
            <p className="mt-1 text-sm text-gray-600">Get occasional notes about product updates and tips.</p>
          </div>

          <form className="w-full sm:w-auto flex gap-3" onSubmit={(e) => { e.preventDefault(); alert('Subscribed!'); }}>
            <label htmlFor="email" className="sr-only">Email</label>
            <input
              id="email"
              type="email"
              required
              placeholder="you@company.com"
              className="min-w-0 px-4 py-3 rounded-xl border border-gray-200 bg-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-300"
            />
            <button className="px-4 py-3 rounded-xl bg-indigo-600 text-white font-medium hover:bg-indigo-700 focus:outline-none">Subscribe</button>
          </form>
        </div>
      </section>

      {/* Simple footer note (content-only — no header or site nav) */}
      <footer className="w-full max-w-5xl mt-12 text-center text-sm text-gray-500">
        <p>© {new Date().getFullYear()} Your Company — Built with care.</p>
      </footer>
    </main>
  );
}
