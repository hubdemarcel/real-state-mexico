# Real Estate Intelligence Dashboard - System Architecture

```mermaid
graph TB
    %% Data Sources Layer
    subgraph "Data Sources"
        DS1[Commercial Portals<br/>Inmuebles24, Propiedades.com<br/>Lamudi, VivaAnuncios<br/>Metros Cúbicos]
        DS2[Governmental & Institutional<br/>INEGI (census, permits)<br/>SHF (housing index)<br/>Banxico (rates, inflation)<br/>SEDATU (urban planning)]
        DS3[Local Authorities<br/>Municipal cadastres<br/>Construction permits<br/>Zoning data]
    end

    %% Data Ingestion Layer
    subgraph "Data Ingestion Layer"
        SCR[Web Scraping<br/>Python + Scrapy<br/>BeautifulSoup]
        API[API Integration<br/>REST APIs<br/>Bulk Downloads]
        ETL[ETL Pipelines<br/>Apache Airflow<br/>Scheduled Jobs]
    end

    %% Data Processing Layer
    subgraph "Data Processing Layer"
        DQ[Data Quality<br/>Validation<br/>Deduplication]
        NORM[Data Harmonization<br/>Normalization<br/>Standardization]
        GEO[Geospatial Processing<br/>GeoPandas<br/>PySpark]
    end

    %% Storage Layer
    subgraph "Storage Layer"
        DB[(MySQL Database<br/>Hostinger Hosting<br/>Geospatial Support)]
        ST[(Local File Storage<br/>Property Images<br/>Documents)]
    end

    %% Analytics & ML Layer
    subgraph "Analytics & ML Layer"
        ML[Machine Learning<br/>Scikit-learn<br/>XGBoost<br/>Facebook Prophet]
        PROC[Data Processing<br/>Pandas<br/>GeoPandas<br/>PySpark]
        PRED[Predictive Models<br/>Price Forecasting<br/>Demand Forecasting]
    end

    %% Backend Layer
    subgraph "Backend Layer"
        APIB[FastAPI Backend<br/>RESTful Services<br/>Model Serving]
        AUTH[Authentication<br/>OAuth2 + JWT<br/>RBAC]
        SEC[Security Layer<br/>Data Anonymization<br/>Compliance]
    end

    %% Frontend Layer
    subgraph "Frontend Layer"
        REACT[React.js Dashboard<br/>Interactive UI<br/>Mapbox GL JS]
        VIZ[Data Visualization<br/>Charts, Maps, KPIs<br/>Heatmaps]
        TOOLS[User Tools<br/>Investment Calculator<br/>Zone Comparison<br/>Report Export]
    end

    %% User Layer
    subgraph "Users"
        INV[Investors<br/>Developers<br/>Financial Analysts]
        BROK[Brokers<br/>Real Estate Agents]
        INST[Institutional Users<br/>Banks, Funds]
    end

    %% Connections
    DS1 --> SCR
    DS2 --> API
    DS3 --> API

    SCR --> ETL
    API --> ETL

    ETL --> DQ
    DQ --> NORM
    NORM --> GEO

    GEO --> DB
    GEO --> ST

    DB --> PROC
    PROC --> ML
    ML --> PRED

    PRED --> APIB
    DB --> APIB

    APIB --> AUTH
    AUTH --> SEC

    SEC --> REACT
    PRED --> REACT
    APIB --> REACT

    REACT --> VIZ
    VIZ --> TOOLS

    TOOLS --> INV
    TOOLS --> BROK
    TOOLS --> INST

    %% Styling
    classDef sourceClass fill:#e1f5fe,stroke:#01579b,stroke-width:2px
    classDef ingestionClass fill:#f3e5f5,stroke:#4a148c,stroke-width:2px
    classDef processingClass fill:#fff3e0,stroke:#e65100,stroke-width:2px
    classDef storageClass fill:#e8f5e8,stroke:#1b5e20,stroke-width:2px
    classDef analyticsClass fill:#fff8e1,stroke:#f57f17,stroke-width:2px
    classDef backendClass fill:#fce4ec,stroke:#880e4f,stroke-width:2px
    classDef frontendClass fill:#f1f8e9,stroke:#33691e,stroke-width:2px
    classDef userClass fill:#e3f2fd,stroke:#0d47a1,stroke-width:2px

    class DS1,DS2,DS3 sourceClass
    class SCR,API,ETL ingestionClass
    class DQ,NORM,GEO processingClass
    class DB,DW,ST storageClass
    class ML,PROC,PRED analyticsClass
    class APIB,AUTH,SEC backendClass
    class REACT,VIZ,TOOLS frontendClass
    class INV,BROK,INST userClass
```

## Architecture Overview

### 1. Data Sources Layer

- **Commercial Portals**: Real estate listing websites
- **Governmental & Institutional**: Official data from INEGI, SHF, Banxico, SEDATU
- **Local Authorities**: Municipal data and cadastres

### 2. Data Ingestion Layer

- **Web Scraping**: Python-based scraping with compliance and rate limiting
- **API Integration**: Direct API connections and bulk data downloads
- **ETL Pipelines**: Apache Airflow for scheduled data processing

### 3. Data Processing Layer

- **Data Quality**: Validation, deduplication, and cleansing
- **Harmonization**: Standardization of disparate data formats
- **Geospatial Processing**: Coordinate systems and spatial analysis

### 4. Storage Layer

- **MySQL Database**: Primary database hosted on Hostinger
- **Geospatial Support**: MySQL spatial extensions for location data
- **Local File Storage**: Property images and document storage

### 5. Analytics & ML Layer

- **Machine Learning**: Scikit-learn, XGBoost for predictive modeling
- **Data Processing**: Pandas, GeoPandas for data manipulation
- **Predictive Models**: Price and demand forecasting

### 6. Backend Layer

- **FastAPI**: High-performance REST API services
- **Authentication**: OAuth2 + JWT with role-based access
- **Security**: Data anonymization and compliance measures

### 7. Frontend Layer

- **React Dashboard**: Modern, responsive user interface
- **Data Visualization**: Interactive charts, maps, and KPIs
- **User Tools**: Calculators, comparisons, and reporting

### 8. User Types

- **Investors & Developers**: Market analysis and investment decisions
- **Brokers & Agents**: Client support and market intelligence
- **Institutional Users**: Banks, investment funds, and analysts

## Key Technical Decisions

| Component     | Technology           | Justification                                  |
| ------------- | -------------------- | ---------------------------------------------- |
| Backend       | FastAPI (Python)     | High performance, async support, auto API docs |
| Database      | MySQL (Hostinger)    | Existing infrastructure, cost-effective        |
| Geospatial    | MySQL Spatial        | Built-in spatial functions for location data   |
| ML Framework  | Scikit-learn/XGBoost | Production-ready, extensive algorithms         |
| Time Series   | Facebook Prophet     | Specialized for forecasting                    |
| Frontend      | React + Mapbox GL JS | Interactive maps, modern UI framework          |
| Hosting       | Hostinger            | Current infrastructure, simplified deployment  |
| Orchestration | Apache Airflow       | Complex workflow management                    |

## Data Flow

1. **Ingestion**: Data collected from multiple sources via APIs and scraping
2. **Processing**: Raw data cleaned, normalized, and enriched with geospatial info
3. **Storage**: Processed data stored in appropriate databases
4. **Analytics**: ML models trained and predictions generated
5. **Serving**: APIs provide data to frontend applications
6. **Visualization**: Interactive dashboards present insights to users

## Security & Compliance

- **Data Anonymization**: Personal data protection per Mexican laws
- **Role-Based Access**: Different permission levels for user types
- **API Security**: JWT tokens, rate limiting, encryption
- **Compliance**: Adherence to Ley Federal de Protección de Datos

## Scalability Considerations

- **Horizontal Scaling**: Containerized services on Kubernetes
- **Data Partitioning**: Time-based and geographical partitioning
- **Caching**: Redis for frequently accessed data
- **CDN**: Global content delivery for static assets

This architecture provides a robust, scalable foundation for the Real Estate Intelligence Dashboard, supporting the complex requirements outlined in the PRD while maintaining performance, security, and user experience standards.
