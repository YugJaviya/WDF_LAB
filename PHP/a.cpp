#include <iostream>
using namespace std;

int main() {
    int a[] = {3, 1, 4, 1, 5};

    for (int i = 1; i < 5; i++) {
        if (a[i] > a[i - 1])
            swap(a[i], a[i - 1]);
    }

    cout << a[1] << " " << a[3];

    return 0;
}


//3

#include <iostream>
using namespace std;

int main() {
    string s = "HACKATHON";

    for (int i = 1; i < s.length(); i += 2)
        cout << s[i];

    return 0;
}


//4 

#include <iostream>
using namespace std;

int main() {
    int a[] = {4, 1, 3, 2};
    int count = 0;

    for (int i = 0; i < 4; i++) {
        for (int j = i + 1; j < 4; j++) {
            if (a[i] > a[j])
                count++;
        }
    }

    cout << count;

    return 0;
}

//5 

int a[] = {10, 20, 30, 40, 50};
int n = 5;

for (int i = 0; i < n / 2; i++) {
    swap(a[i], a[n - i]);
}

//6 

#include <iostream>
using namespace std;

int main() {
    int a[3][3] = {
        {2, 4, 6},
        {1, 3, 5},
        {7, 9, 8}
    };

    int x = 0;

    for (int i = 0; i < 3; i++) {
        for (int j = 0; j < 3; j++) {

            if (i == j)
                x += a[i][j];
            else if (i + j == 2)
                x -= a[i][j];
        }
    }

    cout << x;

    return 0;
}


//7

#include <iostream>
using namespace std;

int main() {

    int a[] = {3, 1, 4, 1, 5};
    int n = 5;

    for (int i = 0; i < n; i++) {

        for (int j = i + 1; j < n; j++) {

            if (a[i] < a[j])
                a[i] = a[j];
        }
    }

    for (int i = 0; i < n; i++)
        cout << a[i] << " ";

    return 0;
}
//9


string s = "HELLO";

for(int i = s.length(); i >= 0; i--)
    cout << s[i];


//10

#include <iostream>
using namespace std;

int change(int x) {
    x = x + 10;
    return x;
}

int main() {

    int a = 5;

    cout << change(a) << " ";
    cout << a;

    return 0;
}


//11

#include <iostream>
using namespace std;

int main() {

    int a[] = {2, 5, 1, 4};

    for(int i = 0; i < 3; i++) {
        if(a[i] < a[i+1])
            a[i+1] = a[i] + a[i+1];
    }

    for(int i = 0; i < 4; i++)
        cout << a[i] << " ";

    return 0;
}

//12
int x=3;
switch(x%2){
    case 0: x+=2;
    case 1: x+=3;
    case 2: x+=2;
    cout<<x;
}

//22 

int x = 1;

switch(x) {
    case 1:
        x += 2;

    case 3:
        x += 4;
        break;

    case 5:
        x += 6;
}

    cout << x;
//23

#include <iostream>
using namespace std;

int main() {
    int a[] = {3, 1, 4, 2};

    for(int i = 0; i < 3; i++) {
        if(a[i] > a[i+1]) {
            a[i] = a[i] - a[i+1];
            a[i+1] = a[i] + a[i+1];
        }
    }

    cout << a[0] << " " << a[1] << " "
         << a[2] << " " << a[3];
}

//35

int x = 2;

switch(x) {
    case 1:
        cout << 1;

    case 2:
        cout << 2;
        break;

    case 3:
        cout << 3;

    default:
        cout << 4;
}

cout << 5;

return 0;

//38

int a[] = {10, 20, 30, 40, 50};
int n = 5;

cout << a[n];


//39

#include <iostream>
using namespace std;

int main() {
    int x = 5;

    switch(x % 3) {
        case 0:
            x += 2;
            break;

        case 1:
            x *= 2;

        case 2:
            x -= 3;
            break;

        default:
            x += 10;
    }

    cout << x;
}

//46

#include <iostream>
#include <string>
using namespace std;

int main() {
    string s = "ABCDE";

    for(int i = 0; i < 5; i++) {
        if((s[i] - 'A') % 2 == 0)
            s[i] = s[i] + 1;
        else
            s[i] = s[i] - 1;
    }

    cout << s;
}


int i = 1;

while(i <= 5) {
    cout << ++i << " ";
    i++;
}


//49

#include <iostream>
using namespace std;

int main() {
    int a = 3;
    int b = 5;

    a++;
    --b;

    int x = a + b++;
    int y = --a + ++b;

    cout << a << " " << b << " ";
    cout << x << " " << y;
}


//43

#include <iostream>
using namespace std;

int main() {
    int a = 3;
    int b = 5;

    a++;
    --b;

    int x = a + b++;
    a = --a + b;

    int y = a++ + --b;

    cout << a << " " << b << " "
         << x << " " << y;
}

//13

#include <iostream>
using namespace std;

int main() {
    string s = "PROGRAMMING";

    for (int i = 0; i < s.length(); i++) {
        if (i % 2 == 0)
            s[i] = '*';
    }

    cout << s;

    return 0;
}


//14

string first = "HACK";
string second = "THON";

string result = first + second;

cout << result;

//15

char ch = 'A';

cout << ch + 2;

//16

char a = 'D';
char b = 'A';

cout << a - b;
//17

char ch = '5';

cout << ch;
cout << ch - '0';


//18

string s = "ABC";

cout << s[0] + s[1] + s[2];

//19

char change(char c)
{
    return c + 1;
}

int main()
{
    string s = "ABC";

    s[1] = change(s[1]);

    cout << s;
}

//24

int main() {
    int x = "Hello";
    cout << x;
}


//25

int main() {
    int a = 10;
    int b = 0;

    cout << a / b;
}

//26

int change(int x)
{
    return x + 5;
}

int main()
{
    int x = 10;

    x = change(x);
    x = change(x);

    cout << x;
}

//27

int calculate(int a, int b)
{
    a = a + b;
    b = a - b;
    return a + b;
}

int main()
{
    int x = 4;
    int y = 7;

    cout << calculate(x, y);
}

//32

int a[] = {2, 4, 6, 8, 10};

for(int i = 0; i < 5; i++)
{
    if(a[i] % 4 == 0)
        a[i] /= 2;
}

cout << a[1] << " " << a[3];


//33

char ch = '7';

cout << ch - '0' + 2;

//34

char next(char c)
{
    return c + 2;
}

int main()
{
    char x = 'M';
    cout << next(x);
}

//40

if(1)
{
cout<<”ABC”;
}
else
{
cout<<”XYZ”;
}
return 0;


//41

For(int i=1;  i ;i++)
{
cout<<”hello”;
}


//43

#include <iostream>
using namespace std;

int main() {
    int a = 10;
    int b = --a + a-- - a++ + ++a;
    
    cout << a << " " << b;
    return 0;
}












