@extends('master')

@section('title', 'ფორუმის წესები და უსაფრთხოების პოლიტიკა')

@section('content')
    <div id="terms-top" class="mx-auto w-full max-w-5xl space-y-6 text-slate-700">
        {{-- HERO --}}
        <header
            class="rounded-2xl border border-slate-200 bg-white px-5 py-6 shadow-sm ring-1 ring-black/5 sm:px-8 sm:py-8">

            <h1 class="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl">
                ფორუმის წესები და უსაფრთხოების პოლიტიკა
            </h1>

            <p class="mt-3 max-w-3xl text-sm sm:text-base leading-7 text-slate-700">
                კეთილი იყოს თქვენი მობრძანება ჩვენს პლატფორმაზე! ფორუმზე რეგისტრაციით თქვენ ადასტურებთ,
                რომ ეთანხმებით ქვემოთ მოცემულ წესებს.
            </p>

            <p class="mt-2 max-w-3xl text-sm leading-7 text-slate-500">
                პლატფორმა იტოვებს უფლებას შეცვალოს წესები წინასწარი გაფრთხილების გარეშე.
                განახლებული ვერსია ძალაში შედის ვებსაიტზე გამოქვეყნებისთანავე.
            </p>
        </header>

        <div class="space-y-6">
            {{-- TOC --}}
            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm ring-1 ring-black/5">
                <div
                    class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2 border-b border-slate-200 px-5 py-4">
                    <div>
                        <h2 class="text-base font-semibold tracking-tight text-slate-900">შინაარსი</h2>
                        <p class="mt-1 text-xs text-slate-500">დააჭირე სათაურს სწრაფი გადასასვლელად</p>
                    </div>
                </div>

                <nav class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-5" aria-label="Table of contents">
                    <a href="#general"
                        class="group rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-3 shadow-inner text-sm text-slate-800 hover:border-slate-300 hover:bg-slate-50 transition">
                        <span class="font-semibold">ზოგადი წესები და ქცევის კოდექსი</span>
                        <span class="block text-xs text-slate-500 group-hover:text-slate-600 mt-1">ქცევის წესები და
                            აკრძალვები</span>
                    </a>
                    <a href="#obligations"
                        class="group rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-3 shadow-inner text-sm text-slate-800 hover:border-slate-300 hover:bg-slate-50 transition">
                        <span class="font-semibold">მომხმარებლის ვალდებულებები</span>
                        <span class="block text-xs text-slate-500 group-hover:text-slate-600 mt-1">კანონიერი და უსაფრთხო
                            გამოყენება</span>
                    </a>
                    <a href="#moderation"
                        class="group rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-3 shadow-inner text-sm text-slate-800 hover:border-slate-300 hover:bg-slate-50 transition">
                        <span class="font-semibold">ადმინისტრირება და სანქციები</span>
                        <span class="block text-xs text-slate-500 group-hover:text-slate-600 mt-1">გაფრთხილება / ბანი /
                            IP-ბლოკი</span>
                    </a>
                    <a href="#privacy"
                        class="group rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-3 shadow-inner text-sm text-slate-800 hover:border-slate-300 hover:bg-slate-50 transition">
                        <span class="font-semibold">კონფიდენციალურობა და მონაცემები</span>
                        <span class="block text-xs text-slate-500 group-hover:text-slate-600 mt-1">რა მონაცემებს ვამუშავებთ
                            და რატომ</span>
                    </a>
                    <a href="#deletion"
                        class="group rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-3 shadow-inner text-sm text-slate-800 hover:border-slate-300 hover:bg-slate-50 transition">
                        <span class="font-semibold">ანგარიშის წაშლა</span>
                        <span class="block text-xs text-slate-500 group-hover:text-slate-600 mt-1">რა რჩება და რა
                            იშლება</span>
                    </a>
                    <a href="#liability"
                        class="group rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-3 shadow-inner text-sm text-slate-800 hover:border-slate-300 hover:bg-slate-50 transition">
                        <span class="font-semibold">პასუხისმგებლობის შეზღუდვა</span>
                        <span class="block text-xs text-slate-500 group-hover:text-slate-600 mt-1">რჩევები — თქვენი
                            რისკით</span>
                    </a>
                    <a href="#ip"
                        class="group sm:col-span-2 rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-3 shadow-inner text-sm text-slate-800 hover:border-slate-300 hover:bg-slate-50 transition">
                        <span class="font-semibold">ინტელექტუალური საკუთრება</span>
                        <span
                            class="block text-xs text-slate-500 group-hover:text-slate-600 mt-1">ლოგო/დიზაინი/ტექსტები/ფოტო/ვიდეო</span>
                    </a>
                </nav>
            </section>

            {{-- SECTION: General --}}
            <section id="general"
                class="scroll-mt-24 rounded-2xl border border-slate-200 bg-white shadow-sm ring-1 ring-black/5">
                <div
                    class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2 border-b border-slate-200 px-5 py-4">
                    <div>
                        <h2 class="text-base font-semibold tracking-tight text-slate-900">ზოგადი წესები და ქცევის კოდექსი
                        </h2>
                        <p class="mt-1 text-xs text-slate-500">ფორუმი პროფესიული დისკუსიებისთვისაა</p>
                    </div>
                </div>

                <div class="p-5 space-y-4">
                    <p class="text-sm leading-7 text-slate-700">
                        ფორუმი შექმნილია გამოცდილების გაზიარებისა და პროფესიული დისკუსიებისთვის. შესაბამისად, აკრძალულია:
                    </p>

                    <ul class="space-y-3 text-sm leading-7 text-slate-700 list-disc pl-5">
                        <li>
                            <span class="font-semibold text-slate-900">შეურაცხყოფა:</span>
                            ნებისმიერი სახის აგრესია, პირადი შეურაცხყოფა ან დისკრიმინაციული განცხადებები სხვა მომხმარებლების
                            მიმართ.
                        </li>
                        <li>
                            <span class="font-semibold text-slate-900">სპამი და რეკლამა:</span>
                            კომერციული რეკლამების განთავსება ადმინისტრაციასთან შეთანხმების გარეშე (გარდა სპეციალურად
                            გამოყოფილი განყოფილებებისა).
                        </li>
                        <li>
                            <span class="font-semibold text-slate-900">არასწორი კატეგორიზაცია:</span>
                            თემების გახსნა იმ სექციებში, რომლებიც არ შეესაბამება შინაარსს.
                        </li>
                        <li><span class="font-semibold text-slate-900">პოლიტიკური ან რელიგიური პროპაგანდა.</span></li>
                        <li>
                            <span class="font-semibold text-slate-900">დეზინფორმაცია:</span>
                            განზრახ მცდარი სამშენებლო რჩევების მიცემა, რამაც შესაძლოა ზიანი მიაყენოს სხვას.
                        </li>
                    </ul>
                </div>
            </section>

            {{-- SECTION: Obligations --}}
            <section id="obligations"
                class="scroll-mt-24 rounded-2xl border border-slate-200 bg-white shadow-sm ring-1 ring-black/5">
                <div
                    class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2 border-b border-slate-200 px-5 py-4">
                    <div>
                        <h2 class="text-base font-semibold tracking-tight text-slate-900">მომხმარებელი ვალდებულია</h2>
                        <p class="mt-1 text-xs text-slate-500">პლატფორმის უსაფრთხო და კანონიერი გამოყენება</p>
                    </div>
                </div>

                <div class="p-5">
                    <ul class="space-y-3 text-sm leading-7 text-slate-700 list-disc pl-5">
                        <li>გამოიყენოს საიტი მხოლოდ კანონიერ მიზნებისთვის.</li>
                        <li>არ განათავსოს ცრუ, შეურაცხმყოფელი ან უკანონო ინფორმაცია.</li>
                        <li>არ დაარღვიოს სხვა პირის ინტელექტუალური საკუთრების უფლებები.</li>
                        <li>არ ჩაერიოს ვებსაიტის ტექნიკურ ფუნქციონირებაში.</li>
                    </ul>
                </div>
            </section>

            {{-- SECTION: Moderation --}}
            <section id="moderation"
                class="scroll-mt-24 rounded-2xl border border-slate-200 bg-white shadow-sm ring-1 ring-black/5">
                <div
                    class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2 border-b border-slate-200 px-5 py-4">
                    <div>
                        <h2 class="text-base font-semibold tracking-tight text-slate-900">ადმინისტრირება და სანქციები</h2>
                        <p class="mt-1 text-xs text-slate-500">დარღვევებზე რეაგირების მექანიზმი</p>
                    </div>
                </div>

                <div class="p-5 space-y-4">
                    <p class="text-sm leading-7 text-slate-700">
                        ფორუმის ადმნინისტრაცია უფლებამოსილია შეზღუდოს ან შეაჩეროს მომხმარებლის წვდომა წესების დარღვევის
                        შემთხვევაში.
                    </p>

                    <p class="text-sm leading-7 text-slate-700">
                        წესების დარღვევის შემთხვევაში, ადმინისტრაცია იტოვებს უფლებას გამოიყენოს შემდეგი ზომები:
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/60 p-4 shadow-inner">
                            <div class="text-sm font-bold">გაფრთხილება</div>
                            <p class="mt-1 text-xs leading-6 text-slate-500">მსუბუქი დარღვევის შემთხვევაში.</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/60 p-4 shadow-inner">
                            <div class="text-sm font-bold">დროებითი ბანი</div>
                            <p class="mt-1 text-xs leading-6 text-slate-500">განმეორებითი დარღვევის ან საშუალო სიმძიმის
                                გადაცდომისას.</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/60 p-4 shadow-inner">
                            <div class="text-sm font-bold">მუდმივი ბანი</div>
                            <p class="mt-1 text-xs leading-6 text-slate-500">სისტემატური დარღვევების, თაღლითობის ან ფორუმის
                                მუშაობის შეფერხების მცდელობისას.</p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- SECTION: Privacy --}}
            <section id="privacy"
                class="scroll-mt-24 rounded-2xl border border-slate-200 bg-white shadow-sm ring-1 ring-black/5">
                <div
                    class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2 border-b border-slate-200 px-5 py-4">
                    <div>
                        <h2 class="text-base font-semibold tracking-tight text-slate-900">კონფიდენციალურობა და მონაცემთა
                            დამუშავება</h2>
                        <p class="mt-1 text-xs text-slate-500">რა მონაცემებს ვამუშავებთ და რატომ</p>
                    </div>
                </div>

                <div class="p-5 space-y-3">
                    {{-- 3.1 --}}
                    <details
                        class="group rounded-2xl border border-slate-200 bg-slate-50/60 open:bg-slate-50 shadow-inner transition">
                        <summary
                            class="cursor-pointer select-none px-4 py-3 text-sm font-semibold text-slate-900 list-none flex items-center justify-between">
                            <span>3.1. ფორუმის მიერ შეიძლება დამუშავდეს შემდეგი მონაცემები</span>
                            <span class="text-slate-500 group-open:rotate-180 transition">⌄</span>
                        </summary>
                        <div class="px-4 pb-4">
                            <ul class="space-y-3 text-sm leading-7 text-slate-700 list-disc pl-5">
                                <li><span class="font-semibold text-slate-900">იდენტიფიკაციის მონაცემები:</span> სახელი,
                                    გვარი; პირადი ნომერი (საჭიროების შემთხვევაში); დაბადების თარიღი.</li>
                                <li><span class="font-semibold text-slate-900">საკონტაქტო მონაცემები:</span> ელექტრონული
                                    ფოსტა; ტელეფონის ნომერი.</li>
                                <li><span class="font-semibold text-slate-900">ტექნიკური მონაცემები:</span> IP მისამართი;
                                    მოწყობილობის ტიპი; ბრაუზერის ინფორმაცია; Cookie-ების მეშვეობით მიღებული ინფორმაცია.</li>
                            </ul>
                        </div>
                    </details>

                    {{-- 3.2 --}}
                    <details
                        class="group rounded-2xl border border-slate-200 bg-slate-50/60 open:bg-slate-50 shadow-inner transition">
                        <summary
                            class="cursor-pointer select-none px-4 py-3 text-sm font-semibold text-slate-900 list-none flex items-center justify-between">
                            <span>3.2. რეგისტრაციისას მოწოდებული ინფორმაცია გამოიყენება მხოლოდ</span>
                            <span class="text-slate-500 group-open:rotate-180 transition">⌄</span>
                        </summary>
                        <div class="px-4 pb-4 space-y-3">
                            <p class="text-sm leading-7 text-slate-700">
                                ჩვენ ვიცავთ თქვენს პერსონალურ მონაცემებს. რეგისტრაციისას თქვენს მიერ მოწოდებული ინფორმაცია
                                (ელ-ფოსტა, სახელი, გვარი) გამოიყენება მხოლოდ:
                            </p>
                            <ul class="space-y-3 text-sm leading-7 text-slate-700 list-disc pl-5">
                                <li>ავტორიზაციისა და უსაფრთხოებისთვის.</li>
                                <li>ფორუმიდან მნიშვნელოვანი შეტყობინებების გამოსაგზავნად.</li>
                                <li>პლატფორმის ფუნქციონირების გასაუმჯობესებლად.</li>
                            </ul>

                            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4">
                                <div class="text-sm font-extrabold text-amber-800">[!მნიშვნელოვანი]</div>
                                <p class="mt-1 text-sm leading-7 text-slate-700">
                                    თქვენი მონაცემები არ გადაეცემა მესამე პირებს, მათ შორის მარკეტინგული მიზნებისთვის გარდა
                                    3.2-ში მითითებული შემთხვევბისა.
                                </p>
                            </div>
                        </div>
                    </details>

                    {{-- 3.3 --}}
                    <details
                        class="group rounded-2xl border border-slate-200 bg-slate-50/60 open:bg-slate-50 shadow-inner transition">
                        <summary
                            class="cursor-pointer select-none px-4 py-3 text-sm font-semibold text-slate-900 list-none flex items-center justify-between">
                            <span>3.3. მონაცემების გადაცემა მესამე პირებისთვის</span>
                            <span class="text-slate-500 group-open:rotate-180 transition">⌄</span>
                        </summary>
                        <div class="px-4 pb-4 space-y-3">
                            <p class="text-sm leading-7 text-slate-700">
                                მონაცემები შეიძლება გადაეცეს: საგადახდო სისტემებს; IT მომსახურების პროვაიდერებს; სახელმწიფო
                                ორგანოებს კანონით გათვალისწინებულ შემთხვევებში.
                            </p>
                            <p class="text-sm leading-7 text-slate-700">
                                მესამე პირებს ეკისრებათ მონაცემთა კონფიდენციალურობის დაცვის ვალდებულება.
                            </p>
                        </div>
                    </details>

                    {{-- 3.4 --}}
                    <details
                        class="group rounded-2xl border border-slate-200 bg-slate-50/60 open:bg-slate-50 shadow-inner transition">
                        <summary
                            class="cursor-pointer select-none px-4 py-3 text-sm font-semibold text-slate-900 list-none flex items-center justify-between">
                            <span>3.4. მონაცემთა დამუშავების მიზნები</span>
                            <span class="text-slate-500 group-open:rotate-180 transition">⌄</span>
                        </summary>
                        <div class="px-4 pb-4">
                            <p class="text-sm leading-7 text-slate-700">
                                მონაცემები მუშავდება შემდეგი მიზნებისთვის: ფორუმში რეგისტრაციისა და მონაწილეობის
                                უზრუნველყოფა; ფორუმის ადმინისტრირების მიზნით; კომუნიკაცია მონაწილეებთან; უსაფრთხოების
                                უზრუნველყოფა; ფორუმის პოპულარიზაცია; კანონით გათვალისწინებული ვალდებულებების შესრულება.
                            </p>
                        </div>
                    </details>

                    {{-- 3.5 --}}
                    <details
                        class="group rounded-2xl border border-slate-200 bg-slate-50/60 open:bg-slate-50 shadow-inner transition">
                        <summary
                            class="cursor-pointer select-none px-4 py-3 text-sm font-semibold text-slate-900 list-none flex items-center justify-between">
                            <span>3.5. მონაცემთა დამუშავება ხორციელდება</span>
                            <span class="text-slate-500 group-open:rotate-180 transition">⌄</span>
                        </summary>
                        <div class="px-4 pb-4">
                            <p class="text-sm leading-7 text-slate-700">
                                მონაცემთა დამუშავება ხორციელდება: სუბიექტის თანხმობის საფუძველზე; ხელშეკრულების შესრულების
                                მიზნით;
                                კანონით გათვალისწინებული ვალდებულებების შესასრულებლად; ფორუმის ლეგიტიმური ინტერესის
                                საფუძველზე.
                            </p>
                        </div>
                    </details>
                </div>
            </section>

            {{-- SECTION: Deletion --}}
            <section id="deletion"
                class="scroll-mt-24 rounded-2xl border border-slate-200 bg-white shadow-sm ring-1 ring-black/5">
                <div
                    class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2 border-b border-slate-200 px-5 py-4">
                    <div>
                        <h2 class="text-base font-semibold tracking-tight text-slate-900">ანგარიშის წაშლა და შინაარსის
                            შენარჩუნება</h2>
                        <p class="mt-1 text-xs text-slate-500">რა ხდება ანგარიშის გაუქმების შემდეგ</p>
                    </div>
                </div>

                <div class="p-5 space-y-3">
                    <p class="text-sm leading-7 text-slate-700">
                        მომხმარებელს ნებისმიერ დროს შეუძლია მოითხოვოს ანგარიშის წაშლა. გაითვალისწინეთ, რომ ანგარიშის
                        გაუქმების შემდეგ:
                    </p>

                    <ul class="space-y-3 text-sm leading-7 text-slate-700 list-disc pl-5">
                        <li>
                            <span class="font-semibold text-slate-900">საჯარო კონტენტი:</span>
                            თქვენს მიერ გახსნილი თემები და დაწერილი კომენტარები არ იშლება. ეს აუცილებელია ფორუმის
                            მთლიანობისა და დისკუსიების ლოგიკური ჯაჭვის შესანარჩუნებლად.
                        </li>
                        <li>
                            <span class="font-semibold text-slate-900">პირადი შეტყობინებები:</span>
                            ყველა პირადი მიმოწერა (PM), რომელიც ინახებოდა თქვენს პროფილში, სრულად წაიშლება და მათი აღდგენა
                            შეუძლებელი იქნება.
                        </li>
                        <li>
                            <span class="font-semibold text-slate-900">პერსონალური მონაცემები:</span>
                            თქვენი ელ-ფოსტა და პაროლი წაიშლება ჩვენი აქტიური ბაზებიდან.
                        </li>
                    </ul>
                </div>
            </section>

            {{-- SECTION: Liability --}}
            <section id="liability"
                class="scroll-mt-24 rounded-2xl border border-slate-200 bg-white shadow-sm ring-1 ring-black/5">
                <div
                    class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2 border-b border-slate-200 px-5 py-4">
                    <div>
                        <h2 class="text-base font-semibold tracking-tight text-slate-900">პასუხისმგებლობის შეზღუდვა</h2>
                        <p class="mt-1 text-xs text-slate-500">რჩევების გამოყენება ხდება თქვენი პასუხისმგებლობით</p>
                    </div>
                </div>

                <div class="p-5">
                    <ul class="space-y-3 text-sm leading-7 text-slate-700 list-disc pl-5">
                        <li>ფორუმის ადმინისტრაცია არ არის პასუხისმგებელი მომხმარებლების მიერ დაწერილ რჩევებზე და ნებისმიერი
                            რჩევის გამოყენება ხდება თქვენივე რისკით.</li>
                        <li>ფორუმის ადმინისტრაცია პასუხს არ აგებს ტექნიკურ შეფერხებებზე, მესამე მხარის მიერ განთავსებულ
                            ინფორმაციაზე, სხვის უკანონო ქმედებაზე და ფორს-მაჟორულ გარემოებებზე.</li>
                    </ul>
                </div>
            </section>

            {{-- SECTION: IP --}}
            <section id="ip"
                class="scroll-mt-24 rounded-2xl border border-slate-200 bg-white shadow-sm ring-1 ring-black/5">
                <div
                    class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2 border-b border-slate-200 px-5 py-4">
                    <div>
                        <h2 class="text-base font-semibold tracking-tight text-slate-900">ინტელექტუალური საკუთრება</h2>
                        <p class="mt-1 text-xs text-slate-500">კონტენტის გამოყენების წესები</p>
                    </div>
                </div>

                <div class="p-5">
                    <ul class="space-y-3 text-sm leading-7 text-slate-700 list-disc pl-5">
                        <li><span class="font-semibold text-slate-900">6.1.</span> ვებსაიტზე განთავსებული ლოგოები, დიზაინი,
                            ტექსტები, ფოტო და ვიდეო მასალა წარმოადგენს ფორუმის საკუთრებას.</li>
                        <li><span class="font-semibold text-slate-900">6.2.</span> აღნიშნული მასალის გამოყენება დასაშვებია
                            მხოლოდ ფორუმის ადმინისტრაციის წინასწარი წერილობითი თანხმობით.</li>
                    </ul>
                </div>
            </section>

            {{-- FOOTER --}}
            <footer class="flex justify-center pt-2">
                <a href="#terms-top"
                    class="inline-flex items-center justify-center rounded-md border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2">
                    ↑ დაბრუნება ზემოთ
                </a>
            </footer>
        </div>
    </div>
@endsection