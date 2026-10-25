import { getLocale } from '@/lib/i18n.svelte';

export type GuideSectionId =
    | 'visa'
    | 'sim'
    | 'money'
    | 'phrases'
    | 'etiquette'
    | 'emergency'
    | 'practical';

export interface GuideItem {
    title: string;
    body: string;
}

export interface GuideSection {
    id: GuideSectionId;
    title: string;
    intro: string;
    items: GuideItem[];
}

const ar: GuideSection[] = [
    {
        id: 'visa',
        title: 'تأشيرة الدخول',
        intro: 'السعودية ترحّب بزوار من أكثر من 60 دولة. تحقق مما ينطبق عليك قبل الحجز.',
        items: [
            {
                title: 'التأشيرة السياحية الإلكترونية',
                body: 'مواطنو أكثر من 60 دولة يمكنهم التقديم عبر المنصة الرسمية (visa.visitsaudi.com). التأشيرة صالحة عادة لمدة عام، مع إقامة تصل إلى 90 يوماً في الزيارة الواحدة.',
            },
            {
                title: 'تأشيرة عند الوصول',
                body: 'متوفرة في المطارات للجنسيات المؤهلة التي تحمل تأشيرة أمريكية أو بريطانية أو شنغن سارية ومستخدمة مرة واحدة على الأقل.',
            },
            {
                title: 'مواطنو دول الخليج',
                body: 'يدخلون بالهوية الوطنية مباشرة دون الحاجة لتأشيرة.',
            },
            {
                title: 'تأشيرة الترانزيت',
                body: 'تأشيرة عبور مجانية لمدة 96 ساعة عند السفر عبر طيران السعودية أو فلاي ناس.',
            },
            {
                title: 'تحقق من المصادر الرسمية',
                body: 'المتطلبات تتغير. تحقق دائماً من المنصة الرسمية للتأشيرات أو سفارة السعودية في بلدك.',
            },
        ],
    },
    {
        id: 'sim',
        title: 'شرائح الاتصال والإنترنت',
        intro: 'تبقى متصلاً بسهولة منذ لحظة وصولك.',
        items: [
            {
                title: 'مشغلو الاتصالات',
                body: 'stc وMobily وZain هم المشغلون الرئيسيون، ولهم أكشاك في جميع المطارات الدولية بعد الجمارك مباشرة.',
            },
            {
                title: 'شريحة السائح',
                body: 'شرائح مسبقة الدفع مخصصة للسياح تشمل بيانات ومكالمات محلية لمدة 7–30 يوماً. يلزم جواز السفر للتسجيل.',
            },
            {
                title: 'الشريحة الإلكترونية eSIM',
                body: 'شرائح السفر الإلكترونية (مثل Airalo وHolafly) تعمل في السعودية إذا كان جوالك يدعمها.',
            },
            {
                title: 'التغطية',
                body: 'تغطية 4G/5G ممتازة في المدن، وقد تقل الإشارة في الطرق الصحراوية النائية.',
            },
        ],
    },
    {
        id: 'money',
        title: 'العملة والصرف',
        intro: 'الريال السعودي (SAR) مربوط بالدولار الأمريكي.',
        items: [
            {
                title: 'العملة',
                body: 'الريال السعودي مربوط بالدولار (1 دولار ≈ 3.75 ريال). الهللة هي الجزء الأصغر (1 ريال = 100 هللة).',
            },
            {
                title: 'البطاقات والصرف الآلي',
                body: 'البطاقات مقبولة في كل مكان تقريباً، وأجهزة الصرف الآلي منتشرة في المدن والمجمعات ومحطات الوقود.',
            },
            {
                title: 'النقد',
                body: 'احمل بعض النقد للأسواق الشعبية والباعة، ومكاتب الصرف موجودة في المجمعات والمطارات.',
            },
            {
                title: 'البقشيش',
                body: 'مستحب وليس إلزامياً — 5–10% في المطاعم شائع.',
            },
        ],
    },
    {
        id: 'phrases',
        title: 'عبارات عربية مفيدة',
        intro: 'كلمات قليلة تفتح لك الأبواب والقلوب.',
        items: [
            { title: 'مرحباً', body: 'Marhaba' },
            { title: 'شكراً', body: 'Shukran' },
            { title: 'بكم؟', body: 'Bikam?' },
            { title: 'أين…؟', body: 'Ayna…?' },
            { title: 'لا أتكلم العربية', body: 'La atakallamu al-ʿarabiyya' },
            { title: 'جميل!', body: 'Jameel!' },
            { title: 'من فضلك / لو سمحت', body: 'Min fadlik' },
        ],
    },
    {
        id: 'etiquette',
        title: 'الإتيكيت والثقافة',
        intro: 'المجتمع السعودي محافظ ومضياف — هذه الإرشادات تساعدك على الاحترام والراحة.',
        items: [
            {
                title: 'اللباس',
                body: 'البس باحتشام في الأماكن العامة: غطِّ الكتفين والركبتين. العباءة غير مطلوبة من الزائرات.',
            },
            {
                title: 'التصوير',
                body: 'استأذن قبل تصوير الأشخاص، خاصة النساء والمباني الحكومية.',
            },
            {
                title: 'أوقات الصلاة',
                body: 'تغلق المتاجر لفترة قصيرة (10–30 دقيقة) أثناء الصلوات الخمس، وصلاة الجمعة تغلق كثيراً من المحلات حتى العصر.',
            },
            {
                title: 'رمضان',
                body: 'يُستحسن الامتناع عن الأكل والشرب والتدخين علناً نهاراً في رمضان، ومعظم المطاعم تفتح بعد المغرب.',
            },
            {
                title: 'نهاية الأسبوع',
                body: 'عطلة الأسبوع الجمعة والسبت. المتاحف والمجمعات أكثر ازدحاماً مساء الخميس والجمعة.',
            },
            {
                title: 'التحية',
                body: 'المصافحة شائعة بين الرجال؛ انتظر حتى تبادر المرأة بالمصافحة، أو ضع يدك على صدرك.',
            },
        ],
    },
    {
        id: 'emergency',
        title: 'أرقام الطوارئ',
        intro: 'احفظ هذه الأرقام قبل وصولك.',
        items: [
            {
                title: '911',
                body: 'الطوارئ الموحدة — الشرطة والإسعاف والدفاع المدني.',
            },
            {
                title: '937',
                body: 'الخط الصحي — مركز اتصال وزارة الصحة.',
            },
            {
                title: '930',
                body: 'مركز اتصال هيئة السياحة السعودية (متعدد اللغات) لاستفسارات ومساعدة السياح.',
            },
            {
                title: 'المستشفيات والصيدليات',
                body: 'مستشفيات حكومية وخاصة وصيدليات تعمل على مدار الساعة في جميع المدن. ابحث عن كلمة «صيدلية».',
            },
        ],
    },
    {
        id: 'practical',
        title: 'معلومات عملية',
        intro: 'تفاصيل صغيرة تجعل رحلتك أسهل.',
        items: [
            {
                title: 'مفتاح الدولة',
                body: '+966.',
            },
            {
                title: 'الكهرباء',
                body: 'مقابس من النوع G (بريطاني) بجهد 220–240 فولت. أحضر محوّلاً.',
            },
            {
                title: 'المناخ',
                body: 'الصيف حار جداً (40+ مئوية في الداخل)، والساحل رطب. الشتاء معتدل، والليالي باردة في المرتفعات.',
            },
            {
                title: 'الماء والمرافق',
                body: 'المياه المعبأة رخيصة، والمصليات ودورات المياه متوفرة على نطاق واسع في المجمعات والمعالم.',
            },
            {
                title: 'التطبيقات',
                body: 'أوبر وكريم وبولت تعمل في المدن. حمّل خرائط دون اتصال لرحلات الصحراء.',
            },
        ],
    },
];

const en: GuideSection[] = [
    {
        id: 'visa',
        title: 'Entry visa',
        intro: 'Saudi Arabia welcomes visitors from more than 60 countries. Check what applies to you before booking.',
        items: [
            {
                title: 'Tourist e-visa',
                body: 'Citizens of 60+ countries can apply online through the official platform (visa.visitsaudi.com). The visa is typically valid for 1 year, with stays of up to 90 days at a time.',
            },
            {
                title: 'Visa on arrival',
                body: 'Available at airports for eligible nationalities holding a valid US, UK or Schengen visa that has been used at least once.',
            },
            {
                title: 'GCC citizens',
                body: 'Citizens of Gulf Cooperation Council countries enter with their national ID — no visa required.',
            },
            {
                title: 'Transit visa',
                body: 'Free 96-hour stopover visa when flying with Saudia or Flynas.',
            },
            {
                title: 'Check official sources',
                body: 'Requirements change. Always verify with the official visa platform or the Saudi embassy in your country.',
            },
        ],
    },
    {
        id: 'sim',
        title: 'SIM cards & internet',
        intro: 'Stay connected easily from the moment you land.',
        items: [
            {
                title: 'Providers',
                body: 'stc, Mobily and Zain are the main operators, with kiosks at all international airports right after customs.',
            },
            {
                title: 'Tourist SIM',
                body: 'Prepaid tourist SIMs include data and local calls for 7–30 days. A passport is required for registration.',
            },
            {
                title: 'eSIM',
                body: 'Travel eSIMs (such as Airalo and Holafly) work in Saudi Arabia if your phone supports them.',
            },
            {
                title: 'Coverage',
                body: '4G/5G coverage is excellent in cities; remote desert roads may have limited signal.',
            },
        ],
    },
    {
        id: 'money',
        title: 'Currency & exchange',
        intro: 'The Saudi Riyal (SAR) is pegged to the US dollar.',
        items: [
            {
                title: 'Currency',
                body: 'The riyal is pegged to the dollar (1 USD ≈ 3.75 SAR). Halalas are the smaller unit (1 riyal = 100 halalas).',
            },
            {
                title: 'Cards & ATMs',
                body: 'Cards are accepted almost everywhere, and ATMs are widespread in cities, malls and fuel stations.',
            },
            {
                title: 'Cash',
                body: 'Carry some cash for souqs and street vendors; exchange offices are in malls and airports.',
            },
            {
                title: 'Tipping',
                body: 'Tipping is appreciated but not obligatory — 5–10% in restaurants is common.',
            },
        ],
    },
    {
        id: 'phrases',
        title: 'Useful Arabic phrases',
        intro: 'A few words open doors and hearts.',
        items: [
            { title: 'Hello', body: 'Marhaba' },
            { title: 'Thank you', body: 'Shukran' },
            { title: 'How much?', body: 'Bikam?' },
            { title: 'Where is…?', body: 'Ayna…?' },
            { title: "I don't speak Arabic", body: 'La atakallamu al-ʿarabiyya' },
            { title: 'Beautiful!', body: 'Jameel!' },
            { title: 'Please / Excuse me', body: 'Min fadlik' },
        ],
    },
    {
        id: 'etiquette',
        title: 'Etiquette & culture',
        intro: 'Saudi society is conservative and hospitable — these tips help you stay respectful and comfortable.',
        items: [
            {
                title: 'Dress',
                body: 'Dress modestly in public: cover shoulders and knees. Abayas are not required for visitors.',
            },
            {
                title: 'Photography',
                body: 'Ask before photographing people, especially women and government buildings.',
            },
            {
                title: 'Prayer times',
                body: 'Shops close briefly (10–30 minutes) during the five daily prayers. Friday prayer closes many businesses until mid-afternoon.',
            },
            {
                title: 'Ramadan',
                body: 'During Ramadan, avoid eating, drinking and smoking in public during daylight. Most restaurants open after sunset.',
            },
            {
                title: 'Weekend',
                body: 'The weekend is Friday–Saturday. Museums and malls are busiest on Thursday and Friday evenings.',
            },
            {
                title: 'Greetings',
                body: 'Handshakes are common between men; wait for women to offer their hand first, or place your hand over your heart.',
            },
        ],
    },
    {
        id: 'emergency',
        title: 'Emergency numbers',
        intro: 'Save these numbers before you arrive.',
        items: [
            {
                title: '911',
                body: 'Unified emergency number — police, ambulance and civil defense.',
            },
            {
                title: '937',
                body: 'Health hotline — Ministry of Health call center.',
            },
            {
                title: '930',
                body: 'Saudi Tourism Authority call center (multi-language) for tourist inquiries and assistance.',
            },
            {
                title: 'Hospitals & pharmacies',
                body: 'Public and private hospitals plus 24-hour pharmacies are available in all cities. Look for "صيدلية".',
            },
        ],
    },
    {
        id: 'practical',
        title: 'Practical info',
        intro: 'Small details that make your trip easier.',
        items: [
            {
                title: 'Country code',
                body: '+966.',
            },
            {
                title: 'Power',
                body: 'Type G sockets (UK style), 220–240V. Bring an adapter.',
            },
            {
                title: 'Climate',
                body: 'Summers are very hot (40°C+ inland) and the coast is humid. Winters are mild, with cool evenings in the highlands.',
            },
            {
                title: 'Water & facilities',
                body: 'Bottled water is cheap; prayer rooms and public toilets are widely available in malls and landmarks.',
            },
            {
                title: 'Apps',
                body: 'Uber, Careem and Bolt work in cities. Download offline maps for desert trips.',
            },
        ],
    },
];

export function guideSections(): GuideSection[] {
    return getLocale() === 'ar' ? ar : en;
}
