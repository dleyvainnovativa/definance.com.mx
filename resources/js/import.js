const dropzone = document.getElementById('dropzone');
const fileInput = document.getElementById('fileInput');
const fileList = document.getElementById('fileList');

let files = [];

let raw_accounts = [{
        "id": 1,
        "name": "ACTIVOS",
        "code": "100",
        "parent_code": 100,
        "parent_id": null,
        "type": "asset"
    },
    {
        "id": 2,
        "name": "ACTIVOS FIJOS",
        "code": "110",
        "parent_code": 110,
        "parent_id": null,
        "type": "asset"
    },
    {
        "id": 3,
        "name": "PASIVOS",
        "code": "200",
        "parent_code": 200,
        "parent_id": null,
        "type": "liability"
    },
    {
        "id": 4,
        "name": "PATRIMONIO",
        "code": "300",
        "parent_code": 300,
        "parent_id": null,
        "type": "equity"
    },
    {
        "id": 5,
        "name": "INGRESOS",
        "code": "400",
        "parent_code": 400,
        "parent_id": null,
        "type": "income"
    },
    {
        "id": 6,
        "name": "EGRESOS",
        "code": "500",
        "parent_code": 500,
        "parent_id": null,
        "type": "expense"
    },
    {
        "id": 7,
        "name": "GASTOS FINANCIEROS",
        "code": "600",
        "parent_code": 600,
        "parent_id": null,
        "type": "expense"
    },
    {
        "id": 8,
        "name": "PRODUCTOS FINANCIEROS",
        "code": "700",
        "parent_code": 700,
        "parent_id": null,
        "type": "income"
    },
    {
        "id": 9,
        "name": "OTROS PRODUCTOS",
        "code": "800",
        "parent_code": 800,
        "parent_id": null,
        "type": "income"
    },
    {
        "id": 10,
        "name": "OTROS GASTOS",
        "code": "900",
        "parent_code": 900,
        "parent_id": null,
        "type": "expense"
    },
    {
        "id": 11,
        "name": "EFECTIVO",
        "code": "100.1",
        "parent_code": 100,
        "parent_id": 1,
        "type": "asset"
    },
    {
        "id": 12,
        "name": "BANCOS",
        "code": "100.2",
        "parent_code": 100,
        "parent_id": 1,
        "type": "asset"
    },
    {
        "id": 13,
        "name": "INVERSIONES",
        "code": "100.3",
        "parent_code": 100,
        "parent_id": 1,
        "type": "asset"
    },
    {
        "id": 14,
        "name": "CUENTAS DE AHORRO",
        "code": "100.4",
        "parent_code": 100,
        "parent_id": 1,
        "type": "asset"
    },
    {
        "id": 15,
        "name": "DEUDORES DIVERSOS",
        "code": "100.5",
        "parent_code": 100,
        "parent_id": 1,
        "type": "asset"
    },
    {
        "id": 16,
        "name": "TERRENOS",
        "code": "110.1",
        "parent_code": 110,
        "parent_id": 2,
        "type": "asset"
    },
    {
        "id": 17,
        "name": "INMUEBLES CASA HABITACION / DEPTO",
        "code": "110.2",
        "parent_code": 110,
        "parent_id": 2,
        "type": "asset"
    },
    {
        "id": 18,
        "name": "EQUIPOS DE TRANSPORTE",
        "code": "110.3",
        "parent_code": 110,
        "parent_id": 2,
        "type": "asset"
    },
    {
        "id": 19,
        "name": "MOBILIARIO Y EQUIPO",
        "code": "110.4",
        "parent_code": 110,
        "parent_id": 2,
        "type": "asset"
    },
    {
        "id": 20,
        "name": "EQUIPOS DE COMPUTO",
        "code": "110.5",
        "parent_code": 110,
        "parent_id": 2,
        "type": "asset"
    },
    {
        "id": 21,
        "name": "OTRO GRUPO",
        "code": "110.6",
        "parent_code": 110,
        "parent_id": 2,
        "type": "asset"
    },
    {
        "id": 22,
        "name": "EQUIPO Y SOFTWARES DE PRODUCCION MUSICAL",
        "code": "110.7",
        "parent_code": 110,
        "parent_id": 2,
        "type": "asset"
    },
    {
        "id": 23,
        "name": "ACTIVOS EN SOCIEDAD CONYUGAL",
        "code": "110.8",
        "parent_code": 110,
        "parent_id": 2,
        "type": "asset"
    },
    {
        "id": 24,
        "name": "TARJETAS DE CREDITO",
        "code": "200.1",
        "parent_code": 200,
        "parent_id": 3,
        "type": "liability"
    },
    {
        "id": 25,
        "name": "PRESTAMOS BANCARIOS",
        "code": "200.2",
        "parent_code": 200,
        "parent_id": 3,
        "type": "liability"
    },
    {
        "id": 26,
        "name": "CREDITOS AUTOMOTRICES",
        "code": "200.3",
        "parent_code": 200,
        "parent_id": 3,
        "type": "liability"
    },
    {
        "id": 27,
        "name": "CREDITOS HIPOTECARIOS",
        "code": "200.4",
        "parent_code": 200,
        "parent_id": 3,
        "type": "liability"
    },
    {
        "id": 28,
        "name": "ACREEDORES DIVERSOS",
        "code": "200.5",
        "parent_code": 200,
        "parent_id": 3,
        "type": "liability"
    },
    {
        "id": 29,
        "name": "DEFICIT O REMANENTE DEL EJERCICIO",
        "code": "300.1",
        "parent_code": 300,
        "parent_id": 4,
        "type": "equity"
    },
    {
        "id": 30,
        "name": "DEFICIT O REMANENTE DE EJERCICIO ANTERIORES",
        "code": "300.2",
        "parent_code": 300,
        "parent_id": 4,
        "type": "equity"
    },
    {
        "id": 31,
        "name": "CARTERA",
        "code": "100.1.1",
        "parent_code": 100.1,
        "parent_id": 11,
        "type": "asset"
    },
    {
        "id": 32,
        "name": "BMX DEBITO",
        "code": "100.2.1",
        "parent_code": 100.2,
        "parent_id": 12,
        "type": "asset"
    },
    {
        "id": 33,
        "name": "NU DEBITO",
        "code": "100.2.2",
        "parent_code": 100.2,
        "parent_id": 12,
        "type": "asset"
    },
    {
        "id": 34,
        "name": "BN DEBITO",
        "code": "100.2.3",
        "parent_code": 100.2,
        "parent_id": 12,
        "type": "asset"
    },
    {
        "id": 35,
        "name": "MERCADO PAGO",
        "code": "100.2.4",
        "parent_code": 100.2,
        "parent_id": 12,
        "type": "asset"
    },
    {
        "id": 36,
        "name": "LA CAGUA GAMER BAR",
        "code": "100.3.1",
        "parent_code": 100.3,
        "parent_id": 13,
        "type": "asset"
    },
    {
        "id": 37,
        "name": "BINANCE",
        "code": "100.3.2",
        "parent_code": 100.3,
        "parent_id": 13,
        "type": "asset"
    },
    {
        "id": 38,
        "name": "CUENTA GBM",
        "code": "100.3.3",
        "parent_code": 100.3,
        "parent_id": 13,
        "type": "asset"
    },
    {
        "id": 39,
        "name": "AHORRO IMPREVISTOS",
        "code": "100.4.1",
        "parent_code": 100.4,
        "parent_id": 14,
        "type": "asset"
    },
    {
        "id": 40,
        "name": "AHORRO VIAJES",
        "code": "100.4.2",
        "parent_code": 100.4,
        "parent_id": 14,
        "type": "asset"
    },
    {
        "id": 41,
        "name": "AIL",
        "code": "100.5.1",
        "parent_code": 100.5,
        "parent_id": 15,
        "type": "asset"
    },
    {
        "id": 42,
        "name": "ID CONTABLE ACTIVO",
        "code": "100.5.2",
        "parent_code": 100.5,
        "parent_id": 15,
        "type": "asset"
    },
    {
        "id": 43,
        "name": "ISAAC",
        "code": "100.5.3",
        "parent_code": 100.5,
        "parent_id": 15,
        "type": "asset"
    },
    {
        "id": 44,
        "name": "NORMA MP",
        "code": "100.5.4",
        "parent_code": 100.5,
        "parent_id": 15,
        "type": "asset"
    },
    {
        "id": 45,
        "name": "AIL PAGO TERCEROS",
        "code": "100.5.5",
        "parent_code": 100.5,
        "parent_id": 15,
        "type": "asset"
    },
    {
        "id": 46,
        "name": "FINANCIAMIENTO IDC",
        "code": "100.5.6",
        "parent_code": 100.5,
        "parent_id": 15,
        "type": "asset"
    },
    {
        "id": 47,
        "name": "AHORROS Y SIMILARES SD",
        "code": "100.5.7",
        "parent_code": 100.5,
        "parent_id": 15,
        "type": "asset"
    },
    {
        "id": 48,
        "name": "OTROS DEUDORES DIVERSOS",
        "code": "100.5.8",
        "parent_code": 100.5,
        "parent_id": 15,
        "type": "asset"
    },
    {
        "id": 49,
        "name": "LIC. VICTOR GUADARRAMA RIESTRA",
        "code": "100.5.9",
        "parent_code": 100.5,
        "parent_id": 15,
        "type": "asset"
    },
    {
        "id": 50,
        "name": "INMUEBLE PALMA REAL CEMPOALA 41",
        "code": "110.2.1",
        "parent_code": 110.2,
        "parent_id": 17,
        "type": "asset"
    },
    {
        "id": 51,
        "name": "KIA RIO 2023 LX TM",
        "code": "110.3.1",
        "parent_code": 110.3,
        "parent_id": 18,
        "type": "asset"
    },
    {
        "id": 52,
        "name": "SUZUKI SWIFT 2025 BOOSTERGREEN TA",
        "code": "110.3.2",
        "parent_code": 110.3,
        "parent_id": 18,
        "type": "asset"
    },
    {
        "id": 53,
        "name": "MITSUBISHI OUTLANDER SPORT 2025 SE PLUS CVT",
        "code": "110.3.3",
        "parent_code": 110.3,
        "parent_id": 18,
        "type": "asset"
    },
    {
        "id": 54,
        "name": "GABINETE NEGRO 4 CAJ TAMAÑO OFICIO",
        "code": "110.4.1",
        "parent_code": 110.4,
        "parent_id": 19,
        "type": "asset"
    },
    {
        "id": 55,
        "name": "PIEZA DECORATIVA AJEDREZ REY NEGRO",
        "code": "110.4.2",
        "parent_code": 110.4,
        "parent_id": 19,
        "type": "asset"
    },
    {
        "id": 56,
        "name": "MESA EN L MADERA CEDRO COLOR NOGAL",
        "code": "110.4.3",
        "parent_code": 110.4,
        "parent_id": 19,
        "type": "asset"
    },
    {
        "id": 57,
        "name": "LIBRERO MADERA CEDRO COLOR NOGAL",
        "code": "110.4.4",
        "parent_code": 110.4,
        "parent_id": 19,
        "type": "asset"
    },
    {
        "id": 58,
        "name": "BUSTO ABSTRACTO DE RESINA",
        "code": "110.4.5",
        "parent_code": 110.4,
        "parent_id": 19,
        "type": "asset"
    },
    {
        "id": 59,
        "name": "PUFF INVIVIDUAL AZUL",
        "code": "110.4.6",
        "parent_code": 110.4,
        "parent_id": 19,
        "type": "asset"
    },
    {
        "id": 60,
        "name": "PUFF INVIVIDUAL ROSA",
        "code": "110.4.7",
        "parent_code": 110.4,
        "parent_id": 19,
        "type": "asset"
    },
    {
        "id": 61,
        "name": "RACK CON MANCUERNAS 05 - 50lbs MARCA TAYGA",
        "code": "110.4.8",
        "parent_code": 110.4,
        "parent_id": 19,
        "type": "asset"
    },
    {
        "id": 62,
        "name": "CUENTA DISPONIBLE",
        "code": "110.4.9",
        "parent_code": 110.4,
        "parent_id": 19,
        "type": "asset"
    },
    {
        "id": 63,
        "name": "CUENTA DISPONIBLE",
        "code": "110.4.10",
        "parent_code": 110.4,
        "parent_id": 19,
        "type": "asset"
    },
    {
        "id": 64,
        "name": "CUENTA DISPONIBLE",
        "code": "110.4.11",
        "parent_code": 110.4,
        "parent_id": 19,
        "type": "asset"
    },
    {
        "id": 65,
        "name": "CUENTA DISPONIBLE",
        "code": "110.4.12",
        "parent_code": 110.4,
        "parent_id": 19,
        "type": "asset"
    },
    {
        "id": 66,
        "name": "CUENTA DISPONIBLE",
        "code": "110.4.13",
        "parent_code": 110.4,
        "parent_id": 19,
        "type": "asset"
    },
    {
        "id": 67,
        "name": "CUENTA DISPONIBLE",
        "code": "110.4.14",
        "parent_code": 110.4,
        "parent_id": 19,
        "type": "asset"
    },
    {
        "id": 68,
        "name": "CUENTA DISPONIBLE",
        "code": "110.4.15",
        "parent_code": 110.4,
        "parent_id": 19,
        "type": "asset"
    },
    {
        "id": 69,
        "name": "CONSOLA PLAY STATION 5 SLIM B/PDISCOS",
        "code": "110.5.1",
        "parent_code": 110.5,
        "parent_id": 20,
        "type": "asset"
    },
    {
        "id": 70,
        "name": "PANTALLA SAMSUNG 27\"",
        "code": "110.5.2",
        "parent_code": 110.5,
        "parent_id": 20,
        "type": "asset"
    },
    {
        "id": 71,
        "name": "MAC MINI M4 PRO CPU 12 NUCELOS GPU 16 NUCLEOS / TECLADO MAGIC Y MOUSE MAGIC",
        "code": "110.5.3",
        "parent_code": 110.5,
        "parent_id": 20,
        "type": "asset"
    },
    {
        "id": 72,
        "name": "LAPTOP LENOVO AMD RYZEN 7 3700U RAM 8GB NUCLEOS 4",
        "code": "110.5.4",
        "parent_code": 110.5,
        "parent_id": 20,
        "type": "asset"
    },
    {
        "id": 73,
        "name": "CPU HP MINI TOWER CORE I7 32GB RAM 512GB SSD",
        "code": "110.5.5",
        "parent_code": 110.5,
        "parent_id": 20,
        "type": "asset"
    },
    {
        "id": 74,
        "name": "PANTALLA LG 65\" OLED 4K UHD",
        "code": "110.5.6",
        "parent_code": 110.5,
        "parent_id": 20,
        "type": "asset"
    },
    {
        "id": 75,
        "name": "IPHONE 17 PRO 256GB",
        "code": "110.5.7",
        "parent_code": 110.5,
        "parent_id": 20,
        "type": "asset"
    },
    {
        "id": 76,
        "name": "CUENTA DISPONIBLE",
        "code": "110.5.8",
        "parent_code": 110.5,
        "parent_id": 20,
        "type": "asset"
    },
    {
        "id": 77,
        "name": "MICROFONO RODE NT1",
        "code": "110.6.1",
        "parent_code": 110.6,
        "parent_id": 21,
        "type": "asset"
    },
    {
        "id": 78,
        "name": "CONTROLADOR MIDI ARTURIA MINILAB 3",
        "code": "110.6.2",
        "parent_code": 110.6,
        "parent_id": 21,
        "type": "asset"
    },
    {
        "id": 79,
        "name": "TECLADO ROLAND EX30",
        "code": "110.6.3",
        "parent_code": 110.6,
        "parent_id": 21,
        "type": "asset"
    },
    {
        "id": 80,
        "name": "QUIJADA DE BURRO",
        "code": "110.6.4",
        "parent_code": 110.6,
        "parent_id": 21,
        "type": "asset"
    },
    {
        "id": 81,
        "name": "THEREMIN MOOG 32 PRESETS",
        "code": "110.6.5",
        "parent_code": 110.6,
        "parent_id": 21,
        "type": "asset"
    },
    {
        "id": 82,
        "name": "GUITARRA YAMAHA APX 600 ELECTROACUSTICA",
        "code": "110.6.6",
        "parent_code": 110.6,
        "parent_id": 21,
        "type": "asset"
    },
    {
        "id": 83,
        "name": "AMPLIFICADOR FENDER RUMBLE 40",
        "code": "110.6.7",
        "parent_code": 110.6,
        "parent_id": 21,
        "type": "asset"
    },
    {
        "id": 84,
        "name": "MICROFONO SENNHEISER E614 SUPERCARDIOIDE",
        "code": "110.6.8",
        "parent_code": 110.6,
        "parent_id": 21,
        "type": "asset"
    },
    {
        "id": 85,
        "name": "GUITARRA AZUL CORDIBA C1",
        "code": "110.6.9",
        "parent_code": 110.6,
        "parent_id": 21,
        "type": "asset"
    },
    {
        "id": 86,
        "name": "CAJON DE PERCUSION TRIBAL",
        "code": "110.6.10",
        "parent_code": 110.6,
        "parent_id": 21,
        "type": "asset"
    },
    {
        "id": 87,
        "name": "FL STUDIO SIGNATURE BLUNDE + UPGRADE TO ALL PLUGINS EDITION",
        "code": "110.7.1",
        "parent_code": 110.7,
        "parent_id": 22,
        "type": "asset"
    },
    {
        "id": 88,
        "name": "PLUGIN SPECTRE + PLUGIN MOTION DIMENSION LITE",
        "code": "110.7.2",
        "parent_code": 110.7,
        "parent_id": 22,
        "type": "asset"
    },
    {
        "id": 89,
        "name": "EZZ DRUMMER 3 + CORE LIBRARY",
        "code": "110.7.3",
        "parent_code": 110.7,
        "parent_id": 22,
        "type": "asset"
    },
    {
        "id": 90,
        "name": "INTERFAZ FOCUSRITE SCARLET 2i2 4TA GENERACION",
        "code": "110.7.4",
        "parent_code": 110.7,
        "parent_id": 22,
        "type": "asset"
    },
    {
        "id": 91,
        "name": "MESA HESTON 2 SILLAS 2 BANCOS",
        "code": "110.8.1",
        "parent_code": 110.8,
        "parent_id": 23,
        "type": "asset"
    },
    {
        "id": 92,
        "name": "SALA MODULAR AZUL EASY LIVING",
        "code": "110.8.2",
        "parent_code": 110.8,
        "parent_id": 23,
        "type": "asset"
    },
    {
        "id": 93,
        "name": "MINISPLIT 1 TON 220V S/F INVERTER MAGNUM 22 (EVAPORADORA Y CONDENSADORA) 07/2023",
        "code": "110.8.3",
        "parent_code": 110.8,
        "parent_id": 23,
        "type": "asset"
    },
    {
        "id": 94,
        "name": "MINISPLIT 1.5 TON 220V S/F INVERTER MAGNUM 22 (EVAPORADORA Y CONDENSADORA) 07/2023",
        "code": "110.8.4",
        "parent_code": 110.8,
        "parent_id": 23,
        "type": "asset"
    },
    {
        "id": 95,
        "name": "MINISPLIT 1 TON 220V S/F INVERTER MAGNUM 22 (EVAPORADORA Y CONDENSADORA) 09/2024",
        "code": "110.8.5",
        "parent_code": 110.8,
        "parent_id": 23,
        "type": "asset"
    },
    {
        "id": 96,
        "name": "MINISPLIT 1 TON 110V S/F INVERTER MAGNUM 22 (EVAPORADORA Y CONDENSADORA) 09/2024",
        "code": "110.8.6",
        "parent_code": 110.8,
        "parent_id": 23,
        "type": "asset"
    },
    {
        "id": 97,
        "name": "REFRIGEDRADO LG 22 NEGROMATE 2 PUERTAS",
        "code": "110.8.7",
        "parent_code": 110.8,
        "parent_id": 23,
        "type": "asset"
    },
    {
        "id": 98,
        "name": "PANTALLA LG 65\" QNED 4K UHD",
        "code": "110.8.8",
        "parent_code": 110.8,
        "parent_id": 23,
        "type": "asset"
    },
    {
        "id": 99,
        "name": "BOCINA BOSE S1 PRO",
        "code": "110.8.9",
        "parent_code": 110.8,
        "parent_id": 23,
        "type": "asset"
    },
    {
        "id": 100,
        "name": "BANCO EJERCICIO ADIDAS",
        "code": "110.8.10",
        "parent_code": 110.8,
        "parent_id": 23,
        "type": "asset"
    },
    {
        "id": 101,
        "name": "CUENTA DISPONIBLE",
        "code": "110.8.11",
        "parent_code": 110.8,
        "parent_id": 23,
        "type": "asset"
    },
    {
        "id": 102,
        "name": "CUENTA DISPONIBLE",
        "code": "110.8.12",
        "parent_code": 110.8,
        "parent_id": 23,
        "type": "asset"
    },
    {
        "id": 103,
        "name": "T.C. LIVERPOOL FGM",
        "code": "200.1.1",
        "parent_code": 200.1,
        "parent_id": 24,
        "type": "liability"
    },
    {
        "id": 104,
        "name": "T.C. RAPPI",
        "code": "200.1.2",
        "parent_code": 200.1,
        "parent_id": 24,
        "type": "liability"
    },
    {
        "id": 105,
        "name": "T.C. NU",
        "code": "200.1.3",
        "parent_code": 200.1,
        "parent_id": 24,
        "type": "liability"
    },
    {
        "id": 106,
        "name": "T.C. LIVERPOOL HOME",
        "code": "200.1.4",
        "parent_code": 200.1,
        "parent_id": 24,
        "type": "liability"
    },
    {
        "id": 107,
        "name": "T.C. COSTCO",
        "code": "200.1.5",
        "parent_code": 200.1,
        "parent_id": 24,
        "type": "liability"
    },
    {
        "id": 108,
        "name": "CUENTA DISPONIBLE",
        "code": "200.2.1",
        "parent_code": 200.2,
        "parent_id": 25,
        "type": "liability"
    },
    {
        "id": 109,
        "name": "CREDITEA",
        "code": "200.2.2",
        "parent_code": 200.2,
        "parent_id": 25,
        "type": "liability"
    },
    {
        "id": 110,
        "name": "CREDITO BANORTE AUTOMOTRIZ",
        "code": "200.3.1",
        "parent_code": 200.3,
        "parent_id": 26,
        "type": "liability"
    },
    {
        "id": 111,
        "name": "CREDITO MITSUBISHI MOTORS FINANCIAL SERVICES",
        "code": "200.3.2",
        "parent_code": 200.3,
        "parent_id": 26,
        "type": "liability"
    },
    {
        "id": 112,
        "name": "CREDITO HIPOTECARIO PALMA REAL CEMPOALA 41",
        "code": "200.4.1",
        "parent_code": 200.4,
        "parent_id": 27,
        "type": "liability"
    },
    {
        "id": 113,
        "name": "ISAAC RET",
        "code": "200.5.1",
        "parent_code": 200.5,
        "parent_id": 28,
        "type": "liability"
    },
    {
        "id": 114,
        "name": "CUENTA DISPONIBLE",
        "code": "200.5.2",
        "parent_code": 200.5,
        "parent_id": 28,
        "type": "liability"
    },
    {
        "id": 115,
        "name": "CETELEM KIA RIO 2023",
        "code": "200.5.3",
        "parent_code": 200.5,
        "parent_id": 28,
        "type": "liability"
    },
    {
        "id": 116,
        "name": "CUENTA DISPONIBLE",
        "code": "200.5.4",
        "parent_code": 200.5,
        "parent_id": 28,
        "type": "liability"
    },
    {
        "id": 117,
        "name": "VICTOR HSBC",
        "code": "200.5.5",
        "parent_code": 200.5,
        "parent_id": 28,
        "type": "liability"
    },
    {
        "id": 118,
        "name": "CUENTA DISPONIBLE",
        "code": "200.5.6",
        "parent_code": 200.5,
        "parent_id": 28,
        "type": "liability"
    },
    {
        "id": 119,
        "name": "ESME RAMIREZ",
        "code": "200.5.7",
        "parent_code": 200.5,
        "parent_id": 28,
        "type": "liability"
    },
    {
        "id": 120,
        "name": "CUENTA NMP",
        "code": "200.5.8",
        "parent_code": 200.5,
        "parent_id": 28,
        "type": "liability"
    },
    {
        "id": 121,
        "name": "AHORROS Y SIMILARES SA",
        "code": "200.5.9",
        "parent_code": 200.5,
        "parent_id": 28,
        "type": "liability"
    },
    {
        "id": 122,
        "name": "AIL PRESTAMOS",
        "code": "200.5.10",
        "parent_code": 200.5,
        "parent_id": 28,
        "type": "liability"
    },
    {
        "id": 123,
        "name": "ID CONTABLE PASIVO",
        "code": "200.5.11",
        "parent_code": 200.5,
        "parent_id": 28,
        "type": "liability"
    },
    {
        "id": 124,
        "name": "OTROS ACREEDORES DIVERSOS",
        "code": "200.5.12",
        "parent_code": 200.5,
        "parent_id": 28,
        "type": "liability"
    },
    {
        "id": 127,
        "name": "REMANENTE O DEFICIT EJERCICIO 2025",
        "code": "300.3",
        "parent_code": 300,
        "parent_id": 4,
        "type": "equity"
    },
    {
        "id": 128,
        "name": "INGRESOS AIL",
        "code": "400.1",
        "parent_code": 400,
        "parent_id": 5,
        "type": "income"
    },
    {
        "id": 129,
        "name": "INGRESOS IDC",
        "code": "400.2",
        "parent_code": 400,
        "parent_id": 5,
        "type": "income"
    },
    {
        "id": 130,
        "name": "INGRESOS ISAAC",
        "code": "400.3",
        "parent_code": 400,
        "parent_id": 5,
        "type": "income"
    },
    {
        "id": 131,
        "name": "INGRESOS RIVEROS",
        "code": "400.4",
        "parent_code": 400,
        "parent_id": 5,
        "type": "income"
    },
    {
        "id": 132,
        "name": "INGRESOS BTG-VIC",
        "code": "400.5",
        "parent_code": 400,
        "parent_id": 5,
        "type": "income"
    },
    {
        "id": 133,
        "name": "OTROS INGRESOS",
        "code": "400.6",
        "parent_code": 400,
        "parent_id": 5,
        "type": "income"
    },
    {
        "id": 134,
        "name": "CUENTA DISPONIBLE",
        "code": "400.7",
        "parent_code": 400,
        "parent_id": 5,
        "type": "income"
    },
    {
        "id": 135,
        "name": "GASTOS ESME",
        "code": "500.1",
        "parent_code": 500,
        "parent_id": 6,
        "type": "expense"
    },
    {
        "id": 136,
        "name": "DESPENSA",
        "code": "500.2",
        "parent_code": 500,
        "parent_id": 6,
        "type": "expense"
    },
    {
        "id": 137,
        "name": "DECORACION INMUEBLE",
        "code": "500.3",
        "parent_code": 500,
        "parent_id": 6,
        "type": "expense"
    },
    {
        "id": 138,
        "name": "MANTTO INMUEBLE",
        "code": "500.4",
        "parent_code": 500,
        "parent_id": 6,
        "type": "expense"
    },
    {
        "id": 139,
        "name": "HIPOTECA MARFIL",
        "code": "500.5",
        "parent_code": 500,
        "parent_id": 6,
        "type": "expense"
    },
    {
        "id": 140,
        "name": "PROYECTO CAFETERIA",
        "code": "500.6",
        "parent_code": 500,
        "parent_id": 6,
        "type": "expense"
    },
    {
        "id": 141,
        "name": "TAXIS Y CAMIONES",
        "code": "500.7",
        "parent_code": 500,
        "parent_id": 6,
        "type": "expense"
    },
    {
        "id": 142,
        "name": "GASTOS TARJETA COSTCO",
        "code": "500.8",
        "parent_code": 500,
        "parent_id": 6,
        "type": "expense"
    },
    {
        "id": 143,
        "name": "COMIDA TRABAJO",
        "code": "500.9",
        "parent_code": 500,
        "parent_id": 6,
        "type": "expense"
    },
    {
        "id": 144,
        "name": "REP DE DAÑOS MAT",
        "code": "500.10",
        "parent_code": 500,
        "parent_id": 6,
        "type": "expense"
    },
    {
        "id": 145,
        "name": "OTROS GASTOS DE LA CASA",
        "code": "500.11",
        "parent_code": 500,
        "parent_id": 6,
        "type": "expense"
    },
    {
        "id": 146,
        "name": "CURSOS Y CAPACITACIONES",
        "code": "500.12",
        "parent_code": 500,
        "parent_id": 6,
        "type": "expense"
    },
    {
        "id": 147,
        "name": "MANTTO EQ TRANSPORTE",
        "code": "500.13",
        "parent_code": 500,
        "parent_id": 6,
        "type": "expense"
    },
    {
        "id": 148,
        "name": "PROYECTO MUSICAL",
        "code": "500.14",
        "parent_code": 500,
        "parent_id": 6,
        "type": "expense"
    },
    {
        "id": 149,
        "name": "GASTOS BEAT",
        "code": "500.15",
        "parent_code": 500,
        "parent_id": 6,
        "type": "expense"
    },
    {
        "id": 150,
        "name": "LIVERPOOL CASA",
        "code": "500.16",
        "parent_code": 500,
        "parent_id": 6,
        "type": "expense"
    },
    {
        "id": 151,
        "name": "CUOTAS Y SUSCRIPCIONES",
        "code": "500.17",
        "parent_code": 500,
        "parent_id": 6,
        "type": "expense"
    },
    {
        "id": 152,
        "name": "RECREACION",
        "code": "500.18",
        "parent_code": 500,
        "parent_id": 6,
        "type": "expense"
    },
    {
        "id": 153,
        "name": "REGALOS EVENTOS Y CUMPLEAÑOS",
        "code": "500.19",
        "parent_code": 500,
        "parent_id": 6,
        "type": "expense"
    },
    {
        "id": 154,
        "name": "SALIDAS CON AMIGOS",
        "code": "500.20",
        "parent_code": 500,
        "parent_id": 6,
        "type": "expense"
    },
    {
        "id": 155,
        "name": "VINOS, CERVEZAS Y LICORES",
        "code": "500.21",
        "parent_code": 500,
        "parent_id": 6,
        "type": "expense"
    },
    {
        "id": 156,
        "name": "ESTETICA Y CUIDADO PERSONAL",
        "code": "500.22",
        "parent_code": 500,
        "parent_id": 6,
        "type": "expense"
    },
    {
        "id": 157,
        "name": "INDUMENTARIA Y ACCESORIOS",
        "code": "500.23",
        "parent_code": 500,
        "parent_id": 6,
        "type": "expense"
    },
    {
        "id": 158,
        "name": "APOYO A TERCEROS",
        "code": "500.24",
        "parent_code": 500,
        "parent_id": 6,
        "type": "expense"
    },
    {
        "id": 159,
        "name": "IMPUESTOS GENERALES",
        "code": "500.25",
        "parent_code": 500,
        "parent_id": 6,
        "type": "expense"
    },
    {
        "id": 160,
        "name": "GASTOS MEDICOS Y DENTALES",
        "code": "500.26",
        "parent_code": 500,
        "parent_id": 6,
        "type": "expense"
    },
    {
        "id": 161,
        "name": "SEGUROS Y FIANZAS",
        "code": "500.27",
        "parent_code": 500,
        "parent_id": 6,
        "type": "expense"
    },
    {
        "id": 162,
        "name": "CASA PALMA REAL",
        "code": "500.28",
        "parent_code": 500,
        "parent_id": 6,
        "type": "expense"
    },
    {
        "id": 163,
        "name": "COMBUSTIBLES Y LUBRICANTES",
        "code": "500.29",
        "parent_code": 500,
        "parent_id": 6,
        "type": "expense"
    },
    {
        "id": 164,
        "name": "PAPELERIA Y ARTICULOS DE OFICINA",
        "code": "500.30",
        "parent_code": 500,
        "parent_id": 6,
        "type": "expense"
    },
    {
        "id": 165,
        "name": "OTROS GASTOS NO IDENTIFICADOS",
        "code": "500.31",
        "parent_code": 500,
        "parent_id": 6,
        "type": "expense"
    },
    {
        "id": 166,
        "name": "OTROS GASTOS ADMINISTRATIVOS",
        "code": "500.32",
        "parent_code": 500,
        "parent_id": 6,
        "type": "expense"
    },
    {
        "id": 167,
        "name": "INTERESES DE FINANCIAMIENTOS RECIBIDOS",
        "code": "600.1",
        "parent_code": 600,
        "parent_id": 7,
        "type": "expense"
    },
    {
        "id": 168,
        "name": "PERDIDA CAMBIARIA",
        "code": "600.2",
        "parent_code": 600,
        "parent_id": 7,
        "type": "expense"
    },
    {
        "id": 169,
        "name": "COMISIONES BANCARIAS",
        "code": "600.3",
        "parent_code": 600,
        "parent_id": 7,
        "type": "expense"
    },
    {
        "id": 170,
        "name": "INTERESES GANADOS EN INVERSIONES",
        "code": "700.1",
        "parent_code": 700,
        "parent_id": 8,
        "type": "income"
    },
    {
        "id": 171,
        "name": "UTILIDAD CAMBIARIA",
        "code": "700.2",
        "parent_code": 700,
        "parent_id": 8,
        "type": "income"
    },
    {
        "id": 172,
        "name": "OTROS PRODUCTOS FINANCIEROS",
        "code": "700.3",
        "parent_code": 700,
        "parent_id": 8,
        "type": "income"
    },
    {
        "id": 173,
        "name": "PERDIDA EN BAJA DE ACTIVOS FIJOS",
        "code": "800.1",
        "parent_code": 800,
        "parent_id": 9,
        "type": "income"
    },
    {
        "id": 174,
        "name": "OTROS GASTOS",
        "code": "800.2",
        "parent_code": 800,
        "parent_id": 9,
        "type": "income"
    },
    {
        "id": 175,
        "name": "UTILIDAD EN VENTAS DE ACTIVOS FIJOS",
        "code": "900.1",
        "parent_code": 900,
        "parent_id": 10,
        "type": "income"
    },
    {
        "id": 176,
        "name": "OTROS PRODUCTOS",
        "code": "900.2",
        "parent_code": 900,
        "parent_id": 10,
        "type": "income"
    }
];

window.raw_accounts = raw_accounts;

dropzone.addEventListener('click', () => fileInput.click());

fileInput.addEventListener('change', (e) => {
    addFiles(e.target.files);
});

dropzone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropzone.classList.add('border-primary');
});

dropzone.addEventListener('dragleave', () => {
    dropzone.classList.remove('border-primary');
});

dropzone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropzone.classList.remove('border-primary');
    addFiles(e.dataTransfer.files);
});

function addFiles(newFiles) {
    for (const file of newFiles) {
        if (!file.name.toLowerCase().endsWith('.xlsx')) {
            alert(`"${file.name}" is not a valid .xlsx file`);
            continue;
        }
        files.push(file);
    }

    updateFileInput();
    renderFileList();
}

function updateFileInput() {
    const dataTransfer = new DataTransfer();
    files.forEach(file => dataTransfer.items.add(file));
    fileInput.files = dataTransfer.files;
}

function renderFileList() {
    fileList.innerHTML = '';

    files.forEach((file, index) => {
        const li = document.createElement('li');
        li.className = 'list-group-item d-flex justify-content-between align-items-center';

        li.innerHTML = `
                <span>${file.name}</span>
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-secondary">
                        Log Data
                    </button>
                    <button type="button" class="btn btn-sm btn-danger">
                        Remove
                    </button>
                </div>
            `;

        // Log XLSX data
        li.querySelector('.btn-secondary').addEventListener('click', () => {
            readXlsx(file);
        });

        // Remove file
        li.querySelector('.btn-danger').addEventListener('click', () => {
            files.splice(index, 1);
            updateFileInput();
            renderFileList();
        });

        fileList.appendChild(li);
    });
}

function readXlsx(file) {
    const reader = new FileReader();

    reader.onload = function (e) {
        const data = new Uint8Array(e.target.result);
        const workbook = XLSX.read(data, {
            type: 'array'
        });

        const firstSheetName = workbook.SheetNames[0];
        const worksheet = workbook.Sheets[firstSheetName];

        // Convert to JSON
        const rows = XLSX.utils.sheet_to_json(worksheet, {
            defval: null,
            raw: false
        });

        // console.group(`📊 XLSX Data: ${file.name}`);
        // // console.table(rows);
        const normalized = rows.map(normalizeRow);
        // console.log(normalized);
        importEntries(normalized);


        const accounts = buildAccounts(rows);


        console.log(rows);
        console.log('📘 Normalized accounts');
        console.log(accounts);
        console.log('📘 Normalized data');
        console.log(normalized);

        // console.log(rows[10]);
        // console.log(normalized[10]);

        // console.groupEnd();
    };

    reader.readAsArrayBuffer(file);
}

function normalizeBoolean(value) {
    if (!value) return false;
    const v = value.toString().toLowerCase();
    return v === 'si';
}

function normalizeNumber(value) {
    if (value === null || value === undefined) return 0;
    if (value === '-') return 0;

    const num = Number(
        value.toString().replace(/,/g, '')
    );

    return isNaN(num) ? 0 : num;
}


function importEntries(data) {
    if (!Array.isArray(data) || data.length === 0) {
        console.warn('No entries to import');
        return;
    }

    const token = localStorage.getItem('finance_auth_token');
    if (!token) return;

    fetch(`${api_url}entries/import`, {
        method: 'POST',
        headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
        body: JSON.stringify(data),
    })
    .then(async response => {
        if (!response.ok) {
            const error = await response.json();
            throw error;
        }
        return response.json();
    })
    .then(result => {
        console.log('Import success:', result);
        alert('Entries imported successfully');
    })
    .catch(error => {
        console.error('Import failed:', error);
        alert('Error importing entries. Check console for details.');
    });
}



function normalizeAccountCode(code) {
    if (!code) return null;

    return code
        .toString()
        .split('-')
        .map(part => String(Number(part))) // remove leading zeros
        .join('.');
}

function extractAccountsFromRow(row) {
    const accounts = [];

    // FORMA PAGO → DEBE
    if (row['FORMA PAGO'] && (row['FORMA PAGO'] !== 'SALDO INICIAL' || row['FORMA PAGO'] !== 'SALDO INICIA CREDITO')) {
        accounts.push({
            parent: row['IDCN2'] ?? null,
            code: normalizeAccountCode(row['ID CONTABLE']),
            name: row['FORMA PAGO'],
            type: row['T.P.']
        });
    }

    // CTA ABONO → HABER
    if (row['CTA ABONO'] && (row['CTA ABONO'] !== 'SALDO INICIAL' || row['CTA ABONO'] !== 'SALDO INICIA CREDITO')) {
        accounts.push({
            parent: row['IDCN2_1'] ?? null,
            code: normalizeAccountCode(row['ID CONTABLE_1']),
            name: row['CTA ABONO'],
            type: row['T.P.']
        });
    }

    return accounts;
}

function normalizeRow(row) {
    let forma_pago_id = getAccountID(formatAccountCode(row['ID CONTABLE']));
    let type = getAccountType(forma_pago_id);
    // let amount_original_debit = row['CARGOS'];
    // let amount_original_credit = row['ABONOS'];
    // let amount = normalizeNumber(row['CARGOS']) || normalizeNumber(row['ABONOS']);
    // console.log(`ORIGINAL: ${amount_original_debit}/${amount_original_credit} - NORMALIZE: ${amount}`);
    return {
        entry_date: row['FECHA'] || null,
        // entry_type: type,
        entry_type: getEntryType(row["T.P."]),
        amount: normalizeNumber(row['CARGOS']) || normalizeNumber(row['ABONOS']),
        debit_account_id: forma_pago_id,
        credit_account_id: getAccountID(formatAccountCode(row['ID CONTABLE_1'])),
        description: row['CONCEPTO'] || null,
        reference: "",
        applies_se: normalizeBoolean(row['APLICA P/SE']),
        applies_fe: normalizeBoolean(row['APLICA P/FE'])

    };
}

function getEntryType(type) {
    if (!type) return 'income';

    const normalized = type
        .toString()
        .trim()
        .toUpperCase();

    const map = {
        'SALDO INICIAL': 'opening_balance',
        'SALDO INICIAL CREDITO': 'opening_balance_credit',
        'INGRESO': 'income',
        'EGRESO': 'expense',
        'TRASPASO INGRESO': 'transfer',
        'TRASPASO EGRESO': 'transfer',
        'ADQ. ACTIVO FIJO': 'asset_acquisition',
    };

    return map[normalized] || 'income';
}


function normalizeRow2(row) {
    let forma_pago_id = getAccountID(formatAccountCode(row['ID CONTABLE']));
    let type = getAccountType(forma_pago_id);
    return {
        // ===== BASIC DATA =====
        fecha: row['FECHA'] || null,

        aplica_se: normalizeBoolean(row['APLICA P/SE']),
        aplica_fe: normalizeBoolean(row['APLICA P/FE']),

        tipo: row['T.P.'] || null,
        concepto: row['CONCEPTO'] || null,

        cargos: normalizeNumber(row['CARGOS']),
        abonos: normalizeNumber(row['ABONOS']),
        saldo: normalizeNumber(row['SALDO']),
        

        // ===== FORMA PAGO =====
        forma_pago: row['FORMA PAGO'] || null,
        forma_pago_code: formatAccountCode(row['ID CONTABLE']),
        forma_pago_id: forma_pago_id,
        forma_pago_type: type,
        forma_pago_parent: formatAccountCode(row['IDCN2']) || null,

        // ===== CTA ABONO =====
        cta_abono: row['CTA ABONO'] || null,
        cta_abono_code: formatAccountCode(row['ID CONTABLE_1']),
        cta_abono_id: getAccountID(formatAccountCode(row['ID CONTABLE_1'])),
        // cta_abono_parent: row['IDCN2_1'] || null,
        cta_abono_parent: formatAccountCode(row['IDCN2_1']) || null,

    };
}
function getAccountID(code) {
    for (let i = 0; i < raw_accounts.length; i++) {
        if (String(raw_accounts[i].code) === String(code)) {
            return raw_accounts[i].id;
        }
    }
    return null;
}
function getAccountType(id) {
    for (let i = 0; i < raw_accounts.length; i++) {
        if (String(raw_accounts[i].id) === String(id)) {
            return raw_accounts[i].type;
        }
    }
    return null;
}


function formatAccountCode(code) {
    if (!code) return null;
    return code
        .replace(/^0+/, '') // remove leading zeros
        .replace(/-/g, '.') // dash → dot
        .replace(/\.0+/g, '.'); // clean segments
}

function isValidAccountName(name) {
    return name && (name.toUpperCase() !== 'SALDO INICIAL' || name.toUpperCase() !== 'SALDO INICIAL CREDITO');
}

function buildAccounts(rows) {
    const accounts = [];
    const accountMap = new Map(); // prevent duplicates

    rows.forEach(row => {
        // ===== FORMA PAGO =====
        if (isValidAccountName(row['FORMA PAGO'])) {
            const code = formatAccountCode(row['ID CONTABLE']);
            const parent = row['IDCN2'];

            if (code && !accountMap.has(code)) {
                const account = {
                    parent: parent || null,
                    code: code,
                    name: row['FORMA PAGO'],
                    type: row['T.P.'] || null
                };

                accountMap.set(code, true);
                accounts.push(account);
            }
        }

        // ===== CTA ABONO =====
        if (isValidAccountName(row['CTA ABONO'])) {
            const code = formatAccountCode(row['ID CONTABLE_1']);
            const parent = row['IDCN2_1'];

            if (code && !accountMap.has(code)) {
                const account = {
                    parent: parent || null,
                    code: code,
                    name: row['CTA ABONO'],
                    type: row['T.P.'] || null
                };

                accountMap.set(code, true);
                accounts.push(account);
            }
        }
    });

    return accounts;
}
