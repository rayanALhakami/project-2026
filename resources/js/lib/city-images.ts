export const cityImages: Record<number, string> = {
    1: '/images/cities/1.jpg',
    2: '/images/cities/2.jpg',
    3: '/images/cities/3.jpg',
    4: '/images/cities/4.jpg',
    5: '/images/cities/5.jpg',
    6: '/images/cities/6.jpg',
    7: '/images/cities/7.jpg',
    8: '/images/cities/8.jpg',
    9: '/images/cities/9.jpg',
};

export function cityImage(id: number): string | undefined {
    return cityImages[id];
}
