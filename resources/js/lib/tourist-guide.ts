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

const fr: GuideSection[] = [
    {
        id: 'visa',
        title: "Visa d'entrée",
        intro: "L'Arabie saoudite accueille les visiteurs de plus de 60 pays. Vérifiez ce qui s'applique à vous avant de réserver.",
        items: [
            {
                title: 'E-visa touristique',
                body: "Les citoyens de plus de 60 pays peuvent faire leur demande en ligne sur la plateforme officielle (visa.visitsaudi.com). Le visa est généralement valable 1 an, avec des séjours jusqu'à 90 jours à la fois.",
            },
            {
                title: "Visa à l'arrivée",
                body: "Disponible dans les aéroports pour les nationalités éligibles détenant un visa américain, britannique ou Schengen valide et déjà utilisé au moins une fois.",
            },
            {
                title: 'Ressortissants du CCG',
                body: 'Les citoyens des pays du Conseil de coopération du Golfe entrent avec leur carte d’identité nationale — sans visa.',
            },
            {
                title: 'Visa de transit',
                body: 'Visa d’escale gratuit de 96 heures en voyageant avec Saudia ou Flynas.',
            },
            {
                title: 'Vérifiez les sources officielles',
                body: 'Les exigences évoluent. Vérifiez toujours la plateforme officielle des visas ou l’ambassade saoudienne dans votre pays.',
            },
        ],
    },
    {
        id: 'sim',
        title: 'Cartes SIM & internet',
        intro: 'Restez connecté facilement dès votre arrivée.',
        items: [
            {
                title: 'Opérateurs',
                body: 'stc, Mobily et Zain sont les principaux opérateurs, avec des kiosques dans tous les aéroports internationaux juste après la douane.',
            },
            {
                title: 'SIM touristique',
                body: 'Les SIM prépayées touristiques incluent données et appels locaux pour 7 à 30 jours. Un passeport est requis pour l’enregistrement.',
            },
            {
                title: 'eSIM',
                body: 'Les eSIM de voyage (comme Airalo et Holafly) fonctionnent en Arabie saoudite si votre téléphone les prend en charge.',
            },
            {
                title: 'Couverture',
                body: 'La couverture 4G/5G est excellente en ville ; les routes désertiques isolées peuvent avoir un signal limité.',
            },
        ],
    },
    {
        id: 'money',
        title: 'Monnaie & change',
        intro: 'Le riyal saoudien (SAR) est indexé sur le dollar américain.',
        items: [
            {
                title: 'Monnaie',
                body: 'Le riyal est indexé sur le dollar (1 USD ≈ 3,75 SAR). Les halalas sont la plus petite unité (1 riyal = 100 halalas).',
            },
            {
                title: 'Cartes & distributeurs',
                body: 'Les cartes sont acceptées presque partout, et les distributeurs sont nombreux en ville, dans les centres commerciaux et les stations-service.',
            },
            {
                title: 'Espèces',
                body: 'Gardez un peu d’espèces pour les souks et les vendeurs ambulants ; les bureaux de change sont dans les centres commerciaux et les aéroports.',
            },
            {
                title: 'Pourboire',
                body: 'Apprécié mais pas obligatoire — 5 à 10 % au restaurant est courant.',
            },
        ],
    },
    {
        id: 'phrases',
        title: 'Phrases arabes utiles',
        intro: 'Quelques mots ouvrent des portes et des cœurs.',
        items: [
            { title: 'Bonjour', body: 'Marhaba' },
            { title: 'Merci', body: 'Shukran' },
            { title: 'Combien ?', body: 'Bikam ?' },
            { title: 'Où est… ?', body: 'Ayna… ?' },
            { title: 'Je ne parle pas arabe', body: 'La atakallamu al-ʿarabiyya' },
            { title: 'Magnifique !', body: 'Jameel !' },
            { title: 'S’il vous plaît', body: 'Min fadlik' },
        ],
    },
    {
        id: 'etiquette',
        title: 'Étiquette & culture',
        intro: 'La société saoudienne est conservatrice et hospitalière — ces conseils vous aident à rester respectueux et à l’aise.',
        items: [
            {
                title: 'Tenue',
                body: 'Habillez-vous sobrement en public : épaules et genoux couverts. L’abaya n’est pas obligatoire pour les visiteuses.',
            },
            {
                title: 'Photographie',
                body: 'Demandez avant de photographier des personnes, en particulier les femmes et les bâtiments officiels.',
            },
            {
                title: 'Heures de prière',
                body: 'Les magasins ferment brièvement (10 à 30 minutes) pendant les cinq prières quotidiennes. La prière du vendredi ferme de nombreux commerces jusqu’au milieu de l’après-midi.',
            },
            {
                title: 'Ramadan',
                body: 'Pendant le Ramadan, évitez de manger, boire et fumer en public la journée. La plupart des restaurants ouvrent après le coucher du soleil.',
            },
            {
                title: 'Week-end',
                body: 'Le week-end est le vendredi et le samedi. Musées et centres commerciaux sont les plus fréquentés les jeudis et vendredis soirs.',
            },
            {
                title: 'Salutations',
                body: 'La poignée de main est courante entre hommes ; attendez que la femme tende la main, ou posez la main sur votre cœur.',
            },
        ],
    },
    {
        id: 'emergency',
        title: "Numéros d'urgence",
        intro: 'Enregistrez ces numéros avant votre arrivée.',
        items: [
            {
                title: '911',
                body: 'Numéro d’urgence unifié — police, ambulance et protection civile.',
            },
            {
                title: '937',
                body: 'Ligne santé — centre d’appel du ministère de la Santé.',
            },
            {
                title: '930',
                body: 'Centre d’appel de l’Autorité saoudienne du tourisme (multilingue) pour les questions et l’assistance des touristes.',
            },
            {
                title: 'Hôpitaux & pharmacies',
                body: 'Des hôpitaux publics et privés et des pharmacies ouvertes 24 h/24 existent dans toutes les villes. Cherchez « صيدلية ».',
            },
        ],
    },
    {
        id: 'practical',
        title: 'Infos pratiques',
        intro: 'De petits détails qui facilitent votre voyage.',
        items: [
            {
                title: 'Indicatif pays',
                body: '+966.',
            },
            {
                title: 'Électricité',
                body: 'Prises de type G (britanniques), 220–240 V. Apportez un adaptateur.',
            },
            {
                title: 'Climat',
                body: 'Les étés sont très chauds (40 °C et plus dans l’intérieur) et la côte est humide. Les hivers sont doux, avec des soirées fraîches dans les hauteurs.',
            },
            {
                title: 'Eau & équipements',
                body: 'L’eau en bouteille est bon marché ; salles de prière et toilettes publiques sont largement disponibles dans les centres commerciaux et les sites.',
            },
            {
                title: 'Applications',
                body: 'Uber, Careem et Bolt fonctionnent en ville. Téléchargez des cartes hors ligne pour les excursions dans le désert.',
            },
        ],
    },
];

const es: GuideSection[] = [
    {
        id: 'visa',
        title: 'Visa de entrada',
        intro: 'Arabia Saudita recibe a visitantes de más de 60 países. Consulta qué aplica en tu caso antes de reservar.',
        items: [
            {
                title: 'E-visa de turista',
                body: 'Ciudadanos de más de 60 países pueden solicitarla en línea en la plataforma oficial (visa.visitsaudi.com). Suele ser válida 1 año, con estancias de hasta 90 días por visita.',
            },
            {
                title: 'Visa a la llegada',
                body: 'Disponible en aeropuertos para nacionalidades elegibles con visa válida de EE. UU., Reino Unido o Schengen usada al menos una vez.',
            },
            {
                title: 'Ciudadanos del CCG',
                body: 'Los ciudadanos de los países del Consejo de Cooperación del Golfo entran con su documento nacional — sin visa.',
            },
            {
                title: 'Visa de tránsito',
                body: 'Escala gratuita de 96 horas al volar con Saudia o Flynas.',
            },
            {
                title: 'Consulta fuentes oficiales',
                body: 'Los requisitos cambian. Verifica siempre la plataforma oficial de visas o la embajada saudí en tu país.',
            },
        ],
    },
    {
        id: 'sim',
        title: 'Tarjetas SIM e internet',
        intro: 'Mantente conectado fácilmente desde que aterrizas.',
        items: [
            {
                title: 'Operadores',
                body: 'stc, Mobily y Zain son los principales operadores, con quioscos en todos los aeropuertos internacionales tras la aduana.',
            },
            {
                title: 'SIM de turista',
                body: 'Las SIM prepago de turista incluyen datos y llamadas locales por 7–30 días. Se requiere pasaporte para registrarse.',
            },
            {
                title: 'eSIM',
                body: 'Las eSIM de viaje (como Airalo y Holafly) funcionan en Arabia Saudita si tu teléfono las admite.',
            },
            {
                title: 'Cobertura',
                body: 'La cobertura 4G/5G es excelente en las ciudades; en carreteras remotas del desierto puede haber poca señal.',
            },
        ],
    },
    {
        id: 'money',
        title: 'Moneda y cambio',
        intro: 'El riyal saudí (SAR) está vinculado al dólar estadounidense.',
        items: [
            {
                title: 'Moneda',
                body: 'El riyal está vinculado al dólar (1 USD ≈ 3,75 SAR). Los halalas son la unidad menor (1 riyal = 100 halalas).',
            },
            {
                title: 'Tarjetas y cajeros',
                body: 'Las tarjetas se aceptan en casi todas partes, y hay cajeros en ciudades, centros comerciales y gasolineras.',
            },
            {
                title: 'Efectivo',
                body: 'Lleva algo de efectivo para los zocos y vendedores ambulantes; hay casas de cambio en centros comerciales y aeropuertos.',
            },
            {
                title: 'Propinas',
                body: 'Se agradecen, pero no son obligatorias — 5–10 % en restaurantes es lo habitual.',
            },
        ],
    },
    {
        id: 'phrases',
        title: 'Frases árabes útiles',
        intro: 'Unas pocas palabras abren puertas y corazones.',
        items: [
            { title: 'Hola', body: 'Marhaba' },
            { title: 'Gracias', body: 'Shukran' },
            { title: '¿Cuánto?', body: '¿Bikam?' },
            { title: '¿Dónde está…?', body: '¿Ayna…?' },
            { title: 'No hablo árabe', body: 'La atakallamu al-ʿarabiyya' },
            { title: '¡Hermoso!', body: '¡Jameel!' },
            { title: 'Por favor / Disculpe', body: 'Min fadlik' },
        ],
    },
    {
        id: 'etiquette',
        title: 'Etiqueta y cultura',
        intro: 'La sociedad saudí es conservadora y hospitalaria: estos consejos te ayudan a ser respetuoso y estar cómodo.',
        items: [
            {
                title: 'Vestimenta',
                body: 'Viste con recato en público: cubre hombros y rodillas. La abaya no es obligatoria para las visitantes.',
            },
            {
                title: 'Fotografía',
                body: 'Pide permiso antes de fotografiar personas, sobre todo mujeres y edificios gubernamentales.',
            },
            {
                title: 'Horas de oración',
                body: 'Las tiendas cierran brevemente (10–30 minutos) durante las cinco oraciones diarias. La oración del viernes cierra muchos comercios hasta media tarde.',
            },
            {
                title: 'Ramadán',
                body: 'Durante el Ramadán evita comer, beber y fumar en público de día. La mayoría de restaurantes abre tras la puesta del sol.',
            },
            {
                title: 'Fin de semana',
                body: 'El fin de semana es viernes y sábado. Museos y centros comerciales se llenan los jueves y viernes por la noche.',
            },
            {
                title: 'Saludos',
                body: 'El apretón de manos es común entre hombres; espera a que la mujer ofrezca la mano, o lleva la tuya al corazón.',
            },
        ],
    },
    {
        id: 'emergency',
        title: 'Números de emergencia',
        intro: 'Guarda estos números antes de llegar.',
        items: [
            {
                title: '911',
                body: 'Emergencias unificadas: policía, ambulancia y defensa civil.',
            },
            {
                title: '937',
                body: 'Línea de salud — centro de atención del Ministerio de Salud.',
            },
            {
                title: '930',
                body: 'Centro de atención de la Autoridad Saudí de Turismo (multilingüe) para consultas y asistencia a turistas.',
            },
            {
                title: 'Hospitales y farmacias',
                body: 'Hay hospitales públicos y privados y farmacias 24 horas en todas las ciudades. Busca «صيدلية».',
            },
        ],
    },
    {
        id: 'practical',
        title: 'Información práctica',
        intro: 'Pequeños detalles que facilitan tu viaje.',
        items: [
            {
                title: 'Prefijo del país',
                body: '+966.',
            },
            {
                title: 'Electricidad',
                body: 'Enchufes tipo G (británicos), 220–240 V. Lleva un adaptador.',
            },
            {
                title: 'Clima',
                body: 'Los veranos son muy calurosos (más de 40 °C en el interior) y la costa es húmeda. Los inviernos son suaves, con noches frescas en las zonas altas.',
            },
            {
                title: 'Agua y servicios',
                body: 'El agua embotellada es barata; salas de oración y baños públicos abundan en centros comerciales y sitios turísticos.',
            },
            {
                title: 'Aplicaciones',
                body: 'Uber, Careem y Bolt funcionan en las ciudades. Descarga mapas sin conexión para excursiones al desierto.',
            },
        ],
    },
];

const de: GuideSection[] = [
    {
        id: 'visa',
        title: 'Einreisevisum',
        intro: 'Saudi-Arabien empfängt Besucher aus über 60 Ländern. Prüfe vor der Buchung, was für dich gilt.',
        items: [
            {
                title: 'Touristen-E-Visum',
                body: 'Bürger aus über 60 Ländern können das Visum online über die offizielle Plattform (visa.visitsaudi.com) beantragen. Es gilt in der Regel 1 Jahr, mit Aufenthalten von bis zu 90 Tagen am Stück.',
            },
            {
                title: 'Visum bei Ankunft',
                body: 'An Flughäfen für berechtigte Nationalitäten mit gültigem US-, UK- oder Schengen-Visum, das mindestens einmal genutzt wurde.',
            },
            {
                title: 'GCC-Bürger',
                body: 'Bürger der Golf-Kooperationsrat-Staaten reisen mit dem Personalausweis ein — kein Visum nötig.',
            },
            {
                title: 'Transitvisum',
                body: 'Kostenloses 96-Stunden-Stoppover-Visum bei Flügen mit Saudia oder Flynas.',
            },
            {
                title: 'Offizielle Quellen prüfen',
                body: 'Anforderungen ändern sich. Prüfe immer die offizielle Visumsplattform oder die saudische Botschaft in deinem Land.',
            },
        ],
    },
    {
        id: 'sim',
        title: 'SIM-Karten & Internet',
        intro: 'Bleibe vom Moment der Landung an einfach verbunden.',
        items: [
            {
                title: 'Anbieter',
                body: 'stc, Mobily und Zain sind die Hauptanbieter, mit Ständen an allen internationalen Flughäfen direkt nach dem Zoll.',
            },
            {
                title: 'Touristen-SIM',
                body: 'Prepaid-Touristen-SIMs enthalten Daten und Inlandsgespräche für 7–30 Tage. Für die Registrierung ist ein Reisepass erforderlich.',
            },
            {
                title: 'eSIM',
                body: 'Reise-eSIMs (z. B. Airalo und Holafly) funktionieren in Saudi-Arabien, wenn dein Telefon sie unterstützt.',
            },
            {
                title: 'Abdeckung',
                body: '4G/5G-Abdeckung ist in Städten ausgezeichnet; abgelegene Wüstenstraßen können nur schwachen Empfang haben.',
            },
        ],
    },
    {
        id: 'money',
        title: 'Währung & Wechsel',
        intro: 'Der Saudi-Riyal (SAR) ist an den US-Dollar gekoppelt.',
        items: [
            {
                title: 'Währung',
                body: 'Der Riyal ist an den Dollar gekoppelt (1 USD ≈ 3,75 SAR). Halalas sind die kleinere Einheit (1 Riyal = 100 Halalas).',
            },
            {
                title: 'Karten & Geldautomaten',
                body: 'Karten werden fast überall akzeptiert; Geldautomaten gibt es in Städten, Einkaufszentren und Tankstellen.',
            },
            {
                title: 'Bargeld',
                body: 'Nimm etwas Bargeld für Souks und Straßenverkäufer mit; Wechselstuben gibt es in Malls und Flughäfen.',
            },
            {
                title: 'Trinkgeld',
                body: 'Gern gesehen, aber nicht verpflichtend — 5–10 % im Restaurant sind üblich.',
            },
        ],
    },
    {
        id: 'phrases',
        title: 'Nützliche arabische Sätze',
        intro: 'Wenige Wörter öffnen Türen und Herzen.',
        items: [
            { title: 'Hallo', body: 'Marhaba' },
            { title: 'Danke', body: 'Shukran' },
            { title: 'Wie viel?', body: 'Bikam?' },
            { title: 'Wo ist…?', body: 'Ayna…?' },
            { title: 'Ich spreche kein Arabisch', body: 'La atakallamu al-ʿarabiyya' },
            { title: 'Wunderschön!', body: 'Jameel!' },
            { title: 'Bitte / Entschuldigung', body: 'Min fadlik' },
        ],
    },
    {
        id: 'etiquette',
        title: 'Etikette & Kultur',
        intro: 'Die saudische Gesellschaft ist konservativ und gastfreundlich — diese Tipps helfen dir, respektvoll und entspannt zu bleiben.',
        items: [
            {
                title: 'Kleidung',
                body: 'Kleide dich in der Öffentlichkeit zurückhaltend: Schultern und Knie bedeckt. Für Besucherinnen ist keine Abaya Pflicht.',
            },
            {
                title: 'Fotografieren',
                body: 'Frage vor dem Fotografieren von Menschen, besonders Frauen und Regierungsgebäuden.',
            },
            {
                title: 'Gebetszeiten',
                body: 'Geschäfte schließen kurz (10–30 Minuten) während der fünf täglichen Gebete. Das Freitagsgebet schließt viele Geschäfte bis zum Nachmittag.',
            },
            {
                title: 'Ramadan',
                body: 'Im Ramadan tagsüber öffentlich nicht essen, trinken oder rauchen. Die meisten Restaurants öffnen nach Sonnenuntergang.',
            },
            {
                title: 'Wochenende',
                body: 'Das Wochenende ist Freitag–Samstag. Museen und Malls sind donnerstags und freitags abends am vollsten.',
            },
            {
                title: 'Begrüßung',
                body: 'Händeschütteln ist unter Männern üblich; warte, bis eine Frau dir die Hand reicht, oder lege die Hand aufs Herz.',
            },
        ],
    },
    {
        id: 'emergency',
        title: 'Notrufnummern',
        intro: 'Speichere diese Nummern vor deiner Ankunft.',
        items: [
            {
                title: '911',
                body: 'Einheitliche Notrufnummer — Polizei, Rettung und Zivilschutz.',
            },
            {
                title: '937',
                body: 'Gesundheitshotline — Callcenter des Gesundheitsministeriums.',
            },
            {
                title: '930',
                body: 'Callcenter der Saudi Tourism Authority (mehrsprachig) für Touristenanfragen und Hilfe.',
            },
            {
                title: 'Krankenhäuser & Apotheken',
                body: 'Öffentliche und private Krankenhäuser sowie 24-Stunden-Apotheken gibt es in allen Städten. Suche nach «صيدلية».',
            },
        ],
    },
    {
        id: 'practical',
        title: 'Praktische Infos',
        intro: 'Kleine Details, die deine Reise leichter machen.',
        items: [
            {
                title: 'Ländervorwahl',
                body: '+966.',
            },
            {
                title: 'Strom',
                body: 'Steckdosen Typ G (britisch), 220–240 V. Nimm einen Adapter mit.',
            },
            {
                title: 'Klima',
                body: 'Die Sommer sind sehr heiß (40 °C+ im Landesinneren), die Küste ist schwül. Die Winter sind mild, mit kühlen Abenden im Hochland.',
            },
            {
                title: 'Wasser & Einrichtungen',
                body: 'Flaschenwasser ist günstig; Gebetsräume und öffentliche Toiletten gibt es in Malls und an Sehenswürdigkeiten.',
            },
            {
                title: 'Apps',
                body: 'Uber, Careem und Bolt funktionieren in Städten. Lade Offline-Karten für Wüstenausflüge herunter.',
            },
        ],
    },
];

const ru: GuideSection[] = [
    {
        id: 'visa',
        title: 'Въездная виза',
        intro: 'Саудовская Аравия принимает гостей из более чем 60 стран. Проверьте, что подходит вам, до бронирования.',
        items: [
            {
                title: 'Туристическая электронная виза',
                body: 'Граждане 60+ стран могут подать заявку онлайн на официальной платформе (visa.visitsaudi.com). Виза обычно действует 1 год, с пребыванием до 90 дней за раз.',
            },
            {
                title: 'Виза по прибытии',
                body: 'Доступна в аэропортах для подходящих национальностей с действующей визой США, Великобритании или Шенгена, использованной хотя бы раз.',
            },
            {
                title: 'Граждане ССАГПЗ',
                body: 'Граждане стран Совета сотрудничества арабских государств Залива въезжают по национальному удостоверению — виза не нужна.',
            },
            {
                title: 'Транзитная виза',
                body: 'Бесплатная стоповер-виза на 96 часов при перелёте Saudia или Flynas.',
            },
            {
                title: 'Проверяйте официальные источники',
                body: 'Требования меняются. Всегда проверяйте официальную платформу виз или посольство Саудовской Аравии в вашей стране.',
            },
        ],
    },
    {
        id: 'sim',
        title: 'SIM-карты и интернет',
        intro: 'Оставайтесь на связи с момента прилёта.',
        items: [
            {
                title: 'Операторы',
                body: 'stc, Mobily и Zain — основные операторы, с киосками во всех международных аэропортах сразу после таможни.',
            },
            {
                title: 'Туристическая SIM',
                body: 'Предоплаченные туристические SIM включают данные и местные звонки на 7–30 дней. Для регистрации нужен загранпаспорт.',
            },
            {
                title: 'eSIM',
                body: 'Туристические eSIM (например, Airalo и Holafly) работают в Саудовской Аравии, если ваш телефон их поддерживает.',
            },
            {
                title: 'Покрытие',
                body: 'Покрытие 4G/5G отличное в городах; на удалённых пустынных дорогах сигнал может быть слабым.',
            },
        ],
    },
    {
        id: 'money',
        title: 'Валюта и обмен',
        intro: 'Саудовский риал (SAR) привязан к доллару США.',
        items: [
            {
                title: 'Валюта',
                body: 'Риал привязан к доллару (1 USD ≈ 3,75 SAR). Халалы — меньшая единица (1 риал = 100 халалов).',
            },
            {
                title: 'Карты и банкоматы',
                body: 'Карты принимают почти везде, банкоматы есть в городах, торговых центрах и на заправках.',
            },
            {
                title: 'Наличные',
                body: 'Имейте немного наличных для рынков и уличных торговцев; обменники есть в ТЦ и аэропортах.',
            },
            {
                title: 'Чаевые',
                body: 'Приветствуются, но не обязательны — 5–10 % в ресторанах обычно.',
            },
        ],
    },
    {
        id: 'phrases',
        title: 'Полезные арабские фразы',
        intro: 'Пара слов открывает двери и сердца.',
        items: [
            { title: 'Здравствуйте', body: 'Marhaba' },
            { title: 'Спасибо', body: 'Shukran' },
            { title: 'Сколько?', body: 'Bikam?' },
            { title: 'Где…?', body: 'Ayna…?' },
            { title: 'Я не говорю по-арабски', body: 'La atakallamu al-ʿarabiyya' },
            { title: 'Красиво!', body: 'Jameel!' },
            { title: 'Пожалуйста / Извините', body: 'Min fadlik' },
        ],
    },
    {
        id: 'etiquette',
        title: 'Этикет и культура',
        intro: 'Саудовское общество консервативно и гостеприимно — эти советы помогут быть уважительным и чувствовать себя комфортно.',
        items: [
            {
                title: 'Одежда',
                body: 'Одевайтесь скромно в общественных местах: плечи и колени закрыты. Абайя для гостей не обязательна.',
            },
            {
                title: 'Фотосъёмка',
                body: 'Спрашивайте разрешение перед съёмкой людей, особенно женщин и государственных зданий.',
            },
            {
                title: 'Время молитв',
                body: 'Магазины закрываются ненадолго (10–30 минут) во время пяти ежедневных молитв. Пятничная молитва закрывает многие магазины до полудня.',
            },
            {
                title: 'Рамадан',
                body: 'В Рамадан днём не ешьте, не пейте и не курите публично. Большинство ресторанов открываются после заката.',
            },
            {
                title: 'Выходные',
                body: 'Выходные — пятница и суббота. Музеи и ТЦ наиболее загружены вечером в четверг и пятницу.',
            },
            {
                title: 'Приветствия',
                body: 'Рукопожатие принято между мужчинами; подождите, пока женщина подаст руку, или приложите руку к сердцу.',
            },
        ],
    },
    {
        id: 'emergency',
        title: 'Экстренные номера',
        intro: 'Сохраните эти номера до прибытия.',
        items: [
            {
                title: '911',
                body: 'Единый экстренный номер — полиция, скорая и гражданская оборона.',
            },
            {
                title: '937',
                body: 'Горячая линия здоровья — колл-центр Министерства здравоохранения.',
            },
            {
                title: '930',
                body: 'Колл-центр Управления туризма Саудовской Аравии (многоязычный) для вопросов и помощи туристам.',
            },
            {
                title: 'Больницы и аптеки',
                body: 'Государственные и частные больницы и круглосуточные аптеки есть во всех городах. Ищите «صيدلية».',
            },
        ],
    },
    {
        id: 'practical',
        title: 'Практическая информация',
        intro: 'Мелочи, которые делают поездку проще.',
        items: [
            {
                title: 'Код страны',
                body: '+966.',
            },
            {
                title: 'Электричество',
                body: 'Розетки типа G (британские), 220–240 В. Возьмите переходник.',
            },
            {
                title: 'Климат',
                body: 'Лето очень жаркое (40 °C+ внутри страны), побережье влажное. Зимы мягкие, по вечерам в горах прохладно.',
            },
            {
                title: 'Вода и удобства',
                body: 'Бутилированная вода дёшева; молельные комнаты и туалеты широко доступны в ТЦ и у достопримечательностей.',
            },
            {
                title: 'Приложения',
                body: 'Uber, Careem и Bolt работают в городах. Скачайте офлайн-карты для пустынных маршрутов.',
            },
        ],
    },
];

const tr: GuideSection[] = [
    {
        id: 'visa',
        title: 'Giriş vizesi',
        intro: 'Suudi Arabistan 60’tan fazla ülkeden ziyaretçi kabul ediyor. Rezervasyondan önce size uyanı kontrol edin.',
        items: [
            {
                title: 'Turist e-vizesi',
                body: '60’tan fazla ülkenin vatandaşı resmî platform üzerinden (visa.visitsaudi.com) çevrimiçi başvurabilir. Vize genellikle 1 yıl geçerlidir ve tek seferde 90 güne kadar kalınabilir.',
            },
            {
                title: 'Varışta vize',
                body: 'En az bir kez kullanılmış geçerli ABD, Birleşik Krallık veya Schengen vizesi olan uygun vatandaşlıklar için havalimanlarında mevcuttur.',
            },
            {
                title: 'KİK vatandaşları',
                body: 'Körfez İşbirliği Konseyi ülkelerinin vatandaşları ulusal kimlikleriyle giriş yapar — vize gerekmez.',
            },
            {
                title: 'Transit vize',
                body: 'Saudia veya Flynas ile uçarken 96 saatlik ücretsiz aktarma vizesi.',
            },
            {
                title: 'Resmî kaynakları kontrol edin',
                body: 'Gereksinimler değişir. Her zaman resmî vize platformunu veya ülkenizdeki Suudi büyükelçiliğini kontrol edin.',
            },
        ],
    },
    {
        id: 'sim',
        title: 'SIM kartlar ve internet',
        intro: 'İndiğiniz andan itibaren kolayca bağlı kalın.',
        items: [
            {
                title: 'Operatörler',
                body: 'stc, Mobily ve Zain ana operatörlerdir; tüm uluslararası havalimanlarında gümrükten hemen sonra büfeleri vardır.',
            },
            {
                title: 'Turist SIM’i',
                body: 'Ön ödemeli turist SIM’leri 7–30 gün boyunca veri ve yerel görüşme içerir. Kayıt için pasaport gerekir.',
            },
            {
                title: 'eSIM',
                body: 'Seyahat eSIM’leri (Airalo, Holafly gibi) telefonunuz destekliyorsa Suudi Arabistan’da çalışır.',
            },
            {
                title: 'Kapsama',
                body: 'Şehirlerde 4G/5G kapsaması mükemmeldir; uzak çöl yollarında sinyal sınırlı olabilir.',
            },
        ],
    },
    {
        id: 'money',
        title: 'Para birimi ve döviz',
        intro: 'Suudi Riyali (SAR) ABD dolarına sabitlenmiştir.',
        items: [
            {
                title: 'Para birimi',
                body: 'Riyal dolara sabittir (1 USD ≈ 3,75 SAR). Halala daha küçük birimdir (1 riyal = 100 halala).',
            },
            {
                title: 'Kartlar ve ATM’ler',
                body: 'Kartlar hemen her yerde geçer; şehirlerde, AVM’lerde ve benzin istasyonlarında ATM’ler yaygındır.',
            },
            {
                title: 'Nakit',
                body: 'Çarşılar ve seyyar satıcılar için biraz nakit bulundurun; döviz büroları AVM’lerde ve havalimanlarındadır.',
            },
            {
                title: 'Bahşiş',
                body: 'Takdir edilir ama zorunlu değildir — restoranlarda %5–10 yaygındır.',
            },
        ],
    },
    {
        id: 'phrases',
        title: 'Faydalı Arapça ifadeler',
        intro: 'Birkaç kelime kapıları ve kalpleri açar.',
        items: [
            { title: 'Merhaba', body: 'Marhaba' },
            { title: 'Teşekkürler', body: 'Shukran' },
            { title: 'Ne kadar?', body: 'Bikam?' },
            { title: 'Nerede…?', body: 'Ayna…?' },
            { title: 'Arapça konuşmuyorum', body: 'La atakallamu al-ʿarabiyya' },
            { title: 'Güzel!', body: 'Jameel!' },
            { title: 'Lütfen / Affedersiniz', body: 'Min fadlik' },
        ],
    },
    {
        id: 'etiquette',
        title: 'Görgü kuralları ve kültür',
        intro: 'Suudi toplumu muhafazakâr ve misafirperverdir — bu ipuçları saygılı ve rahat kalmanıza yardımcı olur.',
        items: [
            {
                title: 'Giyim',
                body: 'Toplum içinde mütevazı giyinin: omuzlar ve dizler kapalı. Ziyaretçiler için abaya zorunlu değildir.',
            },
            {
                title: 'Fotoğraf',
                body: 'İnsanları, özellikle kadınları ve devlet binalarını fotoğraflamadan önce izin isteyin.',
            },
            {
                title: 'Namaz vakitleri',
                body: 'Mağazalar beş vakit namaz sırasında kısa süre (10–30 dakika) kapanır. Cuma namazı birçok işletmeyi ikindiye kadar kapatır.',
            },
            {
                title: 'Ramazan',
                body: 'Ramazan’da gündüz toplum içinde yemek yemekten, içmekten ve sigaradan kaçının. Çoğu restoran gün batımından sonra açılır.',
            },
            {
                title: 'Hafta sonu',
                body: 'Hafta sonu cuma ve cumartesidir. Müzeler ve AVM’ler en yoğun perşembe ve cuma akşamlarıdır.',
            },
            {
                title: 'Selamlaşma',
                body: 'Erkekler arasında el sıkışma yaygındır; kadının elini önce uzatmasını bekleyin veya elinizi kalbinize koyun.',
            },
        ],
    },
    {
        id: 'emergency',
        title: 'Acil durum numaraları',
        intro: 'Varmadan önce bu numaraları kaydedin.',
        items: [
            {
                title: '911',
                body: 'Birleşik acil durum hattı — polis, ambulans ve sivil savunma.',
            },
            {
                title: '937',
                body: 'Sağlık hattı — Sağlık Bakanlığı çağrı merkezi.',
            },
            {
                title: '930',
                body: 'Suudi Turizm Otoritesi çağrı merkezi (çok dilli) — turist soruları ve yardım.',
            },
            {
                title: 'Hastaneler ve eczaneler',
                body: 'Tüm şehirlerde kamu ve özel hastaneler ile 24 saat açık eczaneler vardır. «صيدلية» tabelasını arayın.',
            },
        ],
    },
    {
        id: 'practical',
        title: 'Pratik bilgiler',
        intro: 'Seyahatinizi kolaylaştıran küçük detaylar.',
        items: [
            {
                title: 'Ülke kodu',
                body: '+966.',
            },
            {
                title: 'Elektrik',
                body: 'Tip G prizler (İngiliz tipi), 220–240 V. Adaptör getirin.',
            },
            {
                title: 'İklim',
                body: 'Yazlar çok sıcaktır (iç bölgelerde 40 °C+), kıyı nemlidir. Kışlar ılımandır, yaylalarda akşamlar serindir.',
            },
            {
                title: 'Su ve tesisler',
                body: 'Şişe su ucuzdur; AVM’lerde ve turistik yerlerde mescit ve tuvaletler yaygındır.',
            },
            {
                title: 'Uygulamalar',
                body: 'Şehirlerde Uber, Careem ve Bolt çalışır. Çöl gezileri için çevrimdışı haritalar indirin.',
            },
        ],
    },
];

const zh: GuideSection[] = [
    {
        id: 'visa',
        title: '入境签证',
        intro: '沙特阿拉伯欢迎来自 60 多个国家的游客。预订前请确认适用于您的情况。',
        items: [
            {
                title: '旅游电子签证',
                body: '60 多个国家的公民可通过官方平台（visa.visitsaudi.com）在线申请。签证通常有效期 1 年，单次停留最长 90 天。',
            },
            {
                title: '落地签证',
                body: '持有至少使用过一次的有效美国、英国或申根签证的符合条件国籍人士可在机场办理。',
            },
            {
                title: '海湾国家公民',
                body: '海湾合作委员会成员国公民凭身份证入境——无需签证。',
            },
            {
                title: '过境签证',
                body: '乘坐沙特航空或 Flynas 航班可获得 96 小时免费过境签证。',
            },
            {
                title: '查询官方来源',
                body: '要求会变化。请务必通过官方签证平台或沙特驻您所在国家的大使馆核实。',
            },
        ],
    },
    {
        id: 'sim',
        title: 'SIM 卡与网络',
        intro: '落地即可轻松保持连接。',
        items: [
            {
                title: '运营商',
                body: 'stc、Mobily 和 Zain 是主要运营商，在所有国际机场的海关出口处均设有柜台。',
            },
            {
                title: '游客 SIM 卡',
                body: '预付游客 SIM 卡包含 7–30 天的流量和本地通话。注册需出示护照。',
            },
            {
                title: 'eSIM',
                body: '如果您的手机支持，Airalo、Holafly 等旅行 eSIM 可在沙特使用。',
            },
            {
                title: '信号覆盖',
                body: '城市 4G/5G 覆盖极佳；偏远沙漠道路信号可能较弱。',
            },
        ],
    },
    {
        id: 'money',
        title: '货币与兑换',
        intro: '沙特里亚尔（SAR）与美元挂钩。',
        items: [
            {
                title: '货币',
                body: '里亚尔与美元挂钩（1 美元 ≈ 3.75 里亚尔）。哈拉拉是更小单位（1 里亚尔 = 100 哈拉拉）。',
            },
            {
                title: '银行卡与 ATM',
                body: '几乎所有地方都接受银行卡，城市、商场和加油站的 ATM 随处可见。',
            },
            {
                title: '现金',
                body: '逛集市和向街头摊贩购物请备些现金；商场和机场设有兑换处。',
            },
            {
                title: '小费',
                body: '小费受欢迎但非强制——餐厅一般给 5–10%。',
            },
        ],
    },
    {
        id: 'phrases',
        title: '实用阿拉伯语',
        intro: '几句简单的词就能打开门和人心。',
        items: [
            { title: '你好', body: 'Marhaba' },
            { title: '谢谢', body: 'Shukran' },
            { title: '多少钱？', body: 'Bikam?' },
            { title: '……在哪里？', body: 'Ayna…?' },
            { title: '我不会说阿拉伯语', body: 'La atakallamu al-ʿarabiyya' },
            { title: '真美！', body: 'Jameel!' },
            { title: '请 / 打扰一下', body: 'Min fadlik' },
        ],
    },
    {
        id: 'etiquette',
        title: '礼仪与文化',
        intro: '沙特社会保守而好客——这些提示助您保持尊重与舒适。',
        items: [
            {
                title: '着装',
                body: '公共场所穿着得体：遮盖肩膀和膝盖。女性游客不要求穿黑袍。',
            },
            {
                title: '拍照',
                body: '拍摄他人（尤其是女性和政府建筑）前请先征得同意。',
            },
            {
                title: '礼拜时间',
                body: '每日五次礼拜期间商店会短暂关门（10–30 分钟）。周五礼拜使许多商铺关门至下午。',
            },
            {
                title: '斋月',
                body: '斋月白天请勿在公共场所饮食和吸烟。多数餐厅日落后营业。',
            },
            {
                title: '周末',
                body: '周末是周五和周六。博物馆和商场在周四、周五晚间最拥挤。',
            },
            {
                title: '问候',
                body: '男性间握手很常见；女性主动伸手时再握手，或将手放在胸前。',
            },
        ],
    },
    {
        id: 'emergency',
        title: '紧急电话',
        intro: '抵达前请保存这些号码。',
        items: [
            {
                title: '911',
                body: '统一紧急电话——警察、救护和民防。',
            },
            {
                title: '937',
                body: '健康热线——卫生部呼叫中心。',
            },
            {
                title: '930',
                body: '沙特旅游局呼叫中心（多语种），为游客答疑和提供帮助。',
            },
            {
                title: '医院与药店',
                body: '所有城市都有公立、私立医院和 24 小时药店。请认准「صيدلية」标志。',
            },
        ],
    },
    {
        id: 'practical',
        title: '实用信息',
        intro: '让旅途更轻松的小细节。',
        items: [
            {
                title: '国家代码',
                body: '+966。',
            },
            {
                title: '电源',
                body: 'G 型插座（英式），220–240V。请携带转换插头。',
            },
            {
                title: '气候',
                body: '夏季内陆非常炎热（40°C+），沿海潮湿。冬季温和，高地夜晚凉爽。',
            },
            {
                title: '饮水与设施',
                body: '瓶装水便宜；商场和景点普遍设有礼拜室和公共卫生间。',
            },
            {
                title: '应用程序',
                body: 'Uber、Careem 和 Bolt 在城市可用。沙漠行程请下载离线地图。',
            },
        ],
    },
];

const hi: GuideSection[] = [
    {
        id: 'visa',
        title: 'प्रवेश वीज़ा',
        intro: 'सऊदी अरब 60 से अधिक देशों के पर्यटकों का स्वागत करता है। बुकिंग से पहले अपनी पात्रता जाँचें।',
        items: [
            {
                title: 'पर्यटक ई-वीज़ा',
                body: '60+ देशों के नागरिक आधिकारिक प्लेटफ़ॉर्म (visa.visitsaudi.com) पर ऑनलाइन आवेदन कर सकते हैं। वीज़ा आमतौर पर 1 वर्ष के लिए वैध होता है, एक बार में 90 दिन तक ठहराव के साथ।',
            },
            {
                title: 'आगमन पर वीज़ा',
                body: 'कम से कम एक बार उपयोग किया गया वैध अमेरिकी, ब्रिटिश या शेंगेन वीज़ा रखने वाली पात्र राष्ट्रीयताओं के लिए हवाई अड्डों पर उपलब्ध।',
            },
            {
                title: 'GCC नागरिक',
                body: 'खाड़ी सहयोग परिषद देशों के नागरिक अपने राष्ट्रीय पहचान पत्र से प्रवेश करते हैं — वीज़ा की आवश्यकता नहीं।',
            },
            {
                title: 'ट्रांज़िट वीज़ा',
                body: 'सऊदिया या फ्लाईनास से उड़ान पर 96 घंटे का मुफ़्त स्टॉपओवर वीज़ा।',
            },
            {
                title: 'आधिकारिक स्रोत जाँचें',
                body: 'आवश्यकताएँ बदलती रहती हैं। हमेशा आधिकारिक वीज़ा प्लेटफ़ॉर्म या अपने देश के सऊदी दूतावास से पुष्टि करें।',
            },
        ],
    },
    {
        id: 'sim',
        title: 'सिम कार्ड और इंटरनेट',
        intro: 'उतरते ही आसानी से जुड़े रहें।',
        items: [
            {
                title: 'ऑपरेटर',
                body: 'stc, Mobily और Zain मुख्य ऑपरेटर हैं, सभी अंतरराष्ट्रीय हवाई अड्डों पर कस्टम के ठीक बाद कियोस्क के साथ।',
            },
            {
                title: 'पर्यटक सिम',
                body: 'प्रीपेड पर्यटक सिम में 7–30 दिनों के लिए डेटा और स्थानीय कॉल शामिल हैं। पंजीकरण के लिए पासपोर्ट आवश्यक है।',
            },
            {
                title: 'eSIM',
                body: 'यदि आपका फ़ोन सपोर्ट करता है तो यात्रा eSIM (जैसे Airalo और Holafly) सऊदी अरब में काम करते हैं।',
            },
            {
                title: 'कवरेज',
                body: 'शहरों में 4G/5G कवरेज उत्कृष्ट है; सुदूर रेगिस्तानी सड़कों पर सिग्नल सीमित हो सकता है।',
            },
        ],
    },
    {
        id: 'money',
        title: 'मुद्रा और विनिमय',
        intro: 'सऊदी रियाल (SAR) अमेरिकी डॉलर से जुड़ा है।',
        items: [
            {
                title: 'मुद्रा',
                body: 'रियाल डॉलर से जुड़ा है (1 USD ≈ 3.75 SAR)। हलाला छोटी इकाई है (1 रियाल = 100 हलाला)।',
            },
            {
                title: 'कार्ड और ATM',
                body: 'लगभग हर जगह कार्ड स्वीकार किए जाते हैं; शहरों, मॉल और पेट्रोल पंपों पर ATM व्यापक हैं।',
            },
            {
                title: 'नकद',
                body: 'बाज़ारों और सड़क विक्रेताओं के लिए कुछ नकद रखें; मुद्रा विनिमय कार्यालय मॉल और हवाई अड्डों में हैं।',
            },
            {
                title: 'टिप',
                body: 'टिप की सराहना होती है पर अनिवार्य नहीं — रेस्तरां में 5–10% आम है।',
            },
        ],
    },
    {
        id: 'phrases',
        title: 'उपयोगी अरबी वाक्य',
        intro: 'कुछ शब्द दरवाज़े और दिल खोल देते हैं।',
        items: [
            { title: 'नमस्ते', body: 'Marhaba' },
            { title: 'धन्यवाद', body: 'Shukran' },
            { title: 'कितना?', body: 'Bikam?' },
            { title: 'कहाँ है…?', body: 'Ayna…?' },
            { title: 'मैं अरबी नहीं बोलता', body: 'La atakallamu al-ʿarabiyya' },
            { title: 'सुंदर!', body: 'Jameel!' },
            { title: 'कृपया / माफ़ कीजिए', body: 'Min fadlik' },
        ],
    },
    {
        id: 'etiquette',
        title: 'शिष्टाचार और संस्कृति',
        intro: 'सऊदी समाज रूढ़िवादी और मेहमाननवाज़ है — ये सुझाव आपको सम्मानजनक और सहज रहने में मदद करते हैं।',
        items: [
            {
                title: 'पहनावा',
                body: 'सार्वजनिक स्थानों पर विनम्र कपड़े पहनें: कंधे और घुटने ढके रहें। आगंतुकों के लिए अबाया अनिवार्य नहीं।',
            },
            {
                title: 'फ़ोटोग्राफ़ी',
                body: 'लोगों, विशेषकर महिलाओं और सरकारी भवनों की तस्वीर लेने से पहले अनुमति लें।',
            },
            {
                title: 'नमाज़ के समय',
                body: 'पाँचों नमाज़ों के दौरान दुकानें थोड़ी देर (10–30 मिनट) बंद रहती हैं। जुमे की नमाज़ कई दुकानें दोपहर तक बंद रखती है।',
            },
            {
                title: 'रमज़ान',
                body: 'रमज़ान में दिन के समय सार्वजनिक रूप से खाने, पीने और धूम्रपान से बचें। अधिकांश रेस्तरां सूर्यास्त के बाद खुलते हैं।',
            },
            {
                title: 'सप्ताहांत',
                body: 'सप्ताहांत शुक्रवार–शनिवार है। संग्रहालय और मॉल गुरुवार और शुक्रवार शाम सबसे व्यस्त रहते हैं।',
            },
            {
                title: 'अभिवादन',
                body: 'पुरुषों के बीच हाथ मिलाना आम है; महिला के पहले हाथ बढ़ाने का इंतज़ार करें, या दिल पर हाथ रखें।',
            },
        ],
    },
    {
        id: 'emergency',
        title: 'आपातकालीन नंबर',
        intro: 'पहुँचने से पहले ये नंबर सहेजें।',
        items: [
            {
                title: '911',
                body: 'एकीकृत आपातकालीन नंबर — पुलिस, एम्बुलेंस और नागरिक सुरक्षा।',
            },
            {
                title: '937',
                body: 'स्वास्थ्य हेल्पलाइन — स्वास्थ्य मंत्रालय कॉल सेंटर।',
            },
            {
                title: '930',
                body: 'सऊदी पर्यटन प्राधिकरण कॉल सेंटर (बहुभाषी) — पर्यटकों की पूछताछ और सहायता के लिए।',
            },
            {
                title: 'अस्पताल और फ़ार्मेसी',
                body: 'सभी शहरों में सरकारी और निजी अस्पताल तथा 24 घंटे की फ़ार्मेसी हैं। «صيدلية» चिन्ह देखें।',
            },
        ],
    },
    {
        id: 'practical',
        title: 'व्यावहारिक जानकारी',
        intro: 'छोटी बातें जो यात्रा आसान बनाती हैं।',
        items: [
            {
                title: 'देश कोड',
                body: '+966।',
            },
            {
                title: 'बिजली',
                body: 'टाइप G सॉकेट (ब्रिटिश), 220–240V। एडाप्टर साथ लाएँ।',
            },
            {
                title: 'जलवायु',
                body: 'गर्मियाँ बहुत गर्म होती हैं (अंदरूनी इलाकों में 40°C+), तट उमस भरा है। सर्दियाँ हल्की हैं, पहाड़ों में शामें ठंडी।',
            },
            {
                title: 'पानी और सुविधाएँ',
                body: 'बोतलबंद पानी सस्ता है; मॉल और दर्शनीय स्थलों पर नमाज़ कक्ष और सार्वजनिक शौचालय व्यापक रूप से उपलब्ध हैं।',
            },
            {
                title: 'ऐप्स',
                body: 'शहरों में Uber, Careem और Bolt चलते हैं। रेगिस्तान यात्राओं के लिए ऑफ़लाइन मानचित्र डाउनलोड करें।',
            },
        ],
    },
];

const ur: GuideSection[] = [
    {
        id: 'visa',
        title: 'داخلے کا ویزا',
        intro: 'سعودی عرب 60 سے زائد ممالک کے سیاحوں کا خیر مقدم کرتا ہے۔ بکنگ سے پہلے اپنی اہلیت چیک کریں۔',
        items: [
            {
                title: 'سیاحتی ای ویزا',
                body: '60+ ممالک کے شہری سرکاری پلیٹ فارم (visa.visitsaudi.com) پر آن لائن درخواست دے سکتے ہیں۔ ویزا عام طور پر 1 سال کے لیے کارآمد ہوتا ہے، ایک بار میں 90 دن تک قیام کے ساتھ۔',
            },
            {
                title: 'آمد پر ویزا',
                body: 'کم از کم ایک بار استعمال شدہ درست امریکی، برطانوی یا شینگن ویزا رکھنے والی اہل قومیتوں کے لیے ہوائی اڈوں پر دستیاب۔',
            },
            {
                title: 'خلیجی شہری',
                body: 'خلیج تعاون کونسل کے ممالک کے شہری اپنے قومی شناختی کارڈ سے داخل ہوتے ہیں — ویزا درکار نہیں۔',
            },
            {
                title: 'ٹرانزٹ ویزا',
                body: 'سعودیہ یا فلائی ناس سے سفر پر 96 گھنٹے کا مفت اسٹاپ اوور ویزا۔',
            },
            {
                title: 'سرکاری ذرائع چیک کریں',
                body: 'شرائط بدلتی رہتی ہیں۔ ہمیشہ سرکاری ویزا پلیٹ فارم یا اپنے ملک میں سعودی سفارت خانے سے تصدیق کریں۔',
            },
        ],
    },
    {
        id: 'sim',
        title: 'سم کارڈ اور انٹرنیٹ',
        intro: 'اترنے کے لمحے سے آسانی سے جڑے رہیں۔',
        items: [
            {
                title: 'آپریٹرز',
                body: 'stc، Mobily اور Zain اہم آپریٹرز ہیں، تمام بین الاقوامی ہوائی اڈوں پر کسٹم کے فوراً بعد کیوسک کے ساتھ۔',
            },
            {
                title: 'سیاحتی سم',
                body: 'پری پیڈ سیاحتی سم میں 7–30 دن کے لیے ڈیٹا اور مقامی کالیں شامل ہیں۔ رجسٹریشن کے لیے پاسپورٹ ضروری ہے۔',
            },
            {
                title: 'eSIM',
                body: 'اگر آپ کا فون سپورٹ کرتا ہے تو سفری eSIM (جیسے Airalo اور Holafly) سعودی عرب میں کام کرتے ہیں۔',
            },
            {
                title: 'کوریج',
                body: 'شہروں میں 4G/5G کوریج بہترین ہے؛ دور دراز صحرائی سڑکوں پر سگنل محدود ہو سکتا ہے۔',
            },
        ],
    },
    {
        id: 'money',
        title: 'کرنسی اور تبادلہ',
        intro: 'سعودی ریال (SAR) امریکی ڈالر سے منسلک ہے۔',
        items: [
            {
                title: 'کرنسی',
                body: 'ریال ڈالر سے منسلک ہے (1 USD ≈ 3.75 SAR)۔ ہلالہ چھوٹی اکائی ہے (1 ریال = 100 ہلالہ)۔',
            },
            {
                title: 'کارڈ اور ATM',
                body: 'تقریباً ہر جگہ کارڈ قبول کیے جاتے ہیں؛ شہروں، مالز اور پٹرول پمپس پر ATM عام ہیں۔',
            },
            {
                title: 'نقد',
                body: 'بازاروں اور سڑک کے دکانداروں کے لیے کچھ نقد رکھیں؛ کرنسی ایکسچینج دفاتر مالز اور ہوائی اڈوں میں ہیں۔',
            },
            {
                title: 'ٹپ',
                body: 'ٹپ پسند کی جاتی ہے لیکن لازمی نہیں — ریستورانوں میں 5–10% عام ہے۔',
            },
        ],
    },
    {
        id: 'phrases',
        title: 'مفید عربی جملے',
        intro: 'چند الفاظ دروازے اور دل کھول دیتے ہیں۔',
        items: [
            { title: 'ہیلو', body: 'Marhaba' },
            { title: 'شکریہ', body: 'Shukran' },
            { title: 'کتنے؟', body: 'Bikam?' },
            { title: 'کہاں ہے…؟', body: 'Ayna…?' },
            { title: 'میں عربی نہیں بولتا', body: 'La atakallamu al-ʿarabiyya' },
            { title: 'خوبصورت!', body: 'Jameel!' },
            { title: 'براہ کرم / معاف کیجیے', body: 'Min fadlik' },
        ],
    },
    {
        id: 'etiquette',
        title: 'آداب اور ثقافت',
        intro: 'سعودی معاشرہ قدامت پسند اور مہمان نواز ہے — یہ مشورے آپ کو محترم اور آرام دہ رہنے میں مدد دیتے ہیں۔',
        items: [
            {
                title: 'لباس',
                body: 'عوامی مقامات پر سادہ لباس پہنیں: کندھے اور گھٹنے ڈھکے رہیں۔ زائرین کے لیے عبایہ لازمی نہیں۔',
            },
            {
                title: 'فوٹوگرافی',
                body: 'لوگوں، خاص طور پر خواتین اور سرکاری عمارتوں کی تصویر لینے سے پہلے اجازت لیں۔',
            },
            {
                title: 'نماز کے اوقات',
                body: 'پانچوں نمازوں کے دوران دکانیں مختصر وقت (10–30 منٹ) بند رہتی ہیں۔ جمعہ کی نماز کئی دکانیں سہ پہر تک بند رکھتی ہے۔',
            },
            {
                title: 'رمضان',
                body: 'رمضان میں دن کے وقت عوامی طور پر کھانے، پینے اور تمباکو نوشی سے گریز کریں۔ زیادہ تر ریستوران غروبِ آفتاب کے بعد کھلتے ہیں۔',
            },
            {
                title: 'ویک اینڈ',
                body: 'ویک اینڈ جمعہ اور ہفتہ ہے۔ عجائب گھر اور مال جمعرات اور جمعہ کی شام سب سے مصروف ہوتے ہیں۔',
            },
            {
                title: 'سلام',
                body: 'مردوں کے درمیان مصافحہ عام ہے؛ عورت کے پہلے ہاتھ بڑھانے کا انتظار کریں، یا دل پر ہاتھ رکھیں۔',
            },
        ],
    },
    {
        id: 'emergency',
        title: 'ہنگامی نمبر',
        intro: 'پہنچنے سے پہلے یہ نمبر محفوظ کریں۔',
        items: [
            {
                title: '911',
                body: 'متحدہ ہنگامی نمبر — پولیس، ایمبولینس اور سول ڈیفنس۔',
            },
            {
                title: '937',
                body: 'صحت ہیلپ لائن — وزارتِ صحت کال سینٹر۔',
            },
            {
                title: '930',
                body: 'سعودی ٹورازم اتھارٹی کال سینٹر (کثیر لسانی) — سیاحوں کے سوالات اور مدد کے لیے۔',
            },
            {
                title: 'ہسپتال اور فارمیسیاں',
                body: 'تمام شہروں میں سرکاری اور نجی ہسپتال اور 24 گھنٹے کی فارمیسیاں موجود ہیں۔ «صيدلية» کا نشان تلاش کریں۔',
            },
        ],
    },
    {
        id: 'practical',
        title: 'عملی معلومات',
        intro: 'چھوٹی باتیں جو سفر آسان بناتی ہیں۔',
        items: [
            {
                title: 'ملکی کوڈ',
                body: '+966۔',
            },
            {
                title: 'بجلی',
                body: 'ٹائپ G ساکٹ (برطانوی)، 220–240V۔ اڈاپٹر ساتھ لائیں۔',
            },
            {
                title: 'آب و ہوا',
                body: 'گرمیاں بہت گرم ہوتی ہیں (اندرونی علاقوں میں 40°C+)، ساحل مرطوب ہے۔ سردیاں معتدل ہیں، بلندیوں پر شامیں ٹھنڈی۔',
            },
            {
                title: 'پانی اور سہولیات',
                body: 'بوتل بند پانی سستا ہے؛ مالز اور مقامات پر نماز کے کمرے اور عوامی بیت الخلا وسیع پیمانے پر دستیاب ہیں۔',
            },
            {
                title: 'ایپس',
                body: 'شہروں میں Uber، Careem اور Bolt چلتے ہیں۔ صحرائی سفر کے لیے آف لائن نقشے ڈاؤن لوڈ کریں۔',
            },
        ],
    },
];

const sectionsByLocale: Partial<Record<string, GuideSection[]>> = {
    ar,
    en,
    fr,
    es,
    de,
    ru,
    tr,
    zh,
    hi,
    ur,
};

export function guideSections(): GuideSection[] {
    return sectionsByLocale[getLocale()] ?? en;
}
